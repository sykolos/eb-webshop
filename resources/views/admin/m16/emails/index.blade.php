@extends('layouts.v6.admin')

@section('title', 'Emailközpont')
@section('heading', 'Emailközpont és hírlevél')
@section('intro', 'Tranzakciós levelek és jogosultságos marketingkampányok, queue-ba szervezett küldéssel.')

@section('content')
<div class="v6-section">
    <div class="v6-notice">Staging védelem: a meglévő <code>STAGING_MAIL_TO</code> sink átirányítja a leveleket. Külső szolgáltató és domainhitelesítés még nincs kiválasztva, ezért az éles marketingindítás külön kiadási döntés.</div>
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif
    <div class="v6-stat-grid">
        <div class="v6-stat"><span>Hírlevél címzettek</span><strong>{{ $audienceCount }}</strong></div>
        <div class="v6-stat"><span>Marketing értesítések</span><strong>{{ $marketingCount }}</strong></div>
        <div class="v6-stat"><span>Aktív kampányok</span><strong>{{ $campaigns->whereIn('status', ['scheduled','processing'])->count() }}</strong></div>
    </div>

    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">M16.01 · M16.04</span><h2>Kampány szerkesztése</h2><p>A címzettszám mentéskor és küldéskor is frissül. A megadott HTML a levél törzsében jelenik meg.</p></div></div>
        <form method="POST" action="{{ route('adminpanel.emails.campaigns.store') }}" class="v6-form">
            @csrf
            <div class="v6-form-grid"><label>Kampánynév<input name="name" required maxlength="191" value="{{ old('name') }}"></label><label>Tárgy<input name="subject" required maxlength="191" value="{{ old('subject') }}"></label><label>Szegmens<select name="segment"><option value="newsletter">Hírlevélre feliratkozott</option><option value="marketing">Marketing értesítések</option></select></label><label>Időzóna<select name="timezone"><option>Europe/Budapest</option><option>UTC</option></select></label><label>Ütemezés<input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"></label></div>
            <label>HTML törzs<textarea name="html_body" rows="9" required placeholder="Használható helyettesítők: &#123;&#123;name&#125;&#125;, &#123;&#123;company&#125;&#125;, &#123;&#123;unsubscribe_url&#125;&#125;">{{ old('html_body', '<h2>Aktuális híreink</h2><p>Kedves partnerünk!</p>') }}</textarea></label>
            <label>Szöveges változat<textarea name="text_body" rows="5" placeholder="A HTML levél szöveges alternatívája">{{ old('text_body') }}</textarea></label>
            <details><summary>HTML előnézet</summary><iframe title="Email HTML előnézet" sandbox style="width:100%;min-height:220px;border:1px solid var(--eb-line);background:#fff" srcdoc="{{ old('html_body', '<h2>Aktuális híreink</h2><p>Kedves partnerünk!</p>') }}"></iframe></details>
            <div class="v6-actions"><button class="v6-button v6-button--primary" type="submit">Kampány mentése</button><span class="v6-form-help">Az éles szolgáltatói küldés nincs automatikusan bekapcsolva.</span></div>
        </form>
    </div>

    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">M16.01</span><h2>Tesztküldés</h2><p>Csak a megadott címre indít tesztet; stagingen a sink szabály él.</p></div></div>
        <form method="POST" action="{{ route('adminpanel.emails.test') }}" class="v6-form"><div class="v6-form-grid"><label>Kampány<select name="campaign_id" required>@foreach($campaigns as $campaign)<option value="{{ $campaign->id }}">#{{ $campaign->id }} · {{ $campaign->name }}</option>@endforeach</select></label><label>Tesztcímzett<input type="email" name="email" required placeholder="teszt@example.com"></label></div><button class="v6-button" type="submit">Tesztlevél queue-ba tétele</button></form>
    </div>

    <div class="v6-subsection"><div class="v6-section__header"><div><span class="v6-eyebrow">M16.04 · M16.06</span><h2>Kampányok</h2></div></div><div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Kampány</th><th>Szegmens</th><th>Állapot</th><th>Címzettek</th><th>Elküldve</th><th>Hiba/kihagyva</th><th>Művelet</th></tr></thead><tbody>@forelse($campaigns as $campaign)<tr><td><strong>{{ $campaign->name }}</strong><small>{{ $campaign->subject }}<br>{{ $campaign->scheduled_at?->format('Y-m-d H:i') ?: 'Nincs időzítve' }}</small></td><td>{{ $campaign->segment }}</td><td><span class="v6-status">{{ $campaign->status }}</span></td><td>{{ $campaign->recipient_count }}</td><td>{{ $campaign->sent_count }}</td><td>{{ $campaign->failed_count }} / {{ $campaign->skipped_count }}</td><td><div class="v6-actions">@if(in_array($campaign->status, ['draft','scheduled']))<form method="POST" action="{{ route('adminpanel.emails.campaigns.send', $campaign) }}">@csrf<button class="v6-button v6-button--small v6-button--primary" type="submit">Küldés</button></form>@endif @if(in_array($campaign->status, ['draft','scheduled','processing']))<form method="POST" action="{{ route('adminpanel.emails.campaigns.cancel', $campaign) }}">@csrf<button class="v6-button v6-button--small v6-button--outline" type="submit">Megszakítás</button></form>@endif</div></td></tr>@empty<tr><td colspan="7"><div class="v6-empty"><strong>Még nincs kampány</strong><span>Az első kampány mentése után itt követhető az állapot.</span></div></td></tr>@endforelse</tbody></table></div></div>

    <div class="v6-subsection"><div class="v6-section__header"><div><span class="v6-eyebrow">M16.07</span><h2>Legutóbbi kézbesítések</h2><p>A napló státusza a saját queue- és mailer-folyamat állapotát jelenti; provider kézbesítési/bounce státusz D12 után kapcsolható.</p></div></div><div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Időpont</th><th>Kampány</th><th>Címzett</th><th>Státusz</th><th>Próbálkozás</th><th>Hiba</th></tr></thead><tbody>@forelse($recentDeliveries as $delivery)<tr><td>{{ $delivery->created_at?->format('Y-m-d H:i') }}</td><td>{{ $delivery->campaign?->name ?: 'Teszt' }}</td><td>{{ $delivery->email }}</td><td>{{ $delivery->status }}</td><td>{{ $delivery->attempts }}</td><td>{{ $delivery->last_error }}</td></tr>@empty<tr><td colspan="6">Még nincs kézbesítési rekord.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
