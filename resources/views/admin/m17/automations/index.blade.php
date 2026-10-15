@extends('layouts.v6.admin')

@section('title', 'Automatizmusok')
@section('heading', 'Automatizmusok')
@section('intro')<p>Időzített kosáremlékeztetők és eseményértesítések központi kezelése.</p>@endsection

@section('content')
<div class="v6-section">
    <div class="v6-notice">Az új szabályok alapértelmezetten kikapcsolva indulnak. A staging környezetben a meglévő mail-sink védelem marad érvényben; a ticket-értesítés szabálya az M18 ticket domainjéig csak előkészített, nem fut.</div>
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif
    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">M17.01 · M17.05 · M17.06</span><h2>Szabályok</h2><p>A futás újraellenőrzi a kosár állapotát, a címzettet és a preferenciát küldés előtt.</p></div></div>
        <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Szabály</th><th>Esemény</th><th>Bekapcsolva</th><th>Küszöb</th><th>Ablak / időzóna</th><th>Maximum</th><th>Következő futás</th><th>Művelet</th></tr></thead><tbody>
        @foreach($rules as $rule)
            <tr><td><strong>{{ $rule->name }}</strong><small>{{ $rule->key }}</small></td><td>{{ $rule->event }}</td><td><span class="v6-status v6-status--{{ $rule->enabled ? 'success' : 'neutral' }}">{{ $rule->enabled ? 'Aktív' : 'Szünetel' }}</span></td><td>{{ $rule->delay_minutes }} perc</td><td>{{ $rule->window_start && $rule->window_end ? $rule->window_start.'–'.$rule->window_end : 'Nincs korlátozás' }}<small>{{ $rule->timezone }}</small></td><td>{{ $rule->max_per_user }} / időszak</td><td>{{ $rule->next_run_at?->format('Y-m-d H:i') ?: 'Scheduler szerint' }}</td><td><div class="v6-actions"><form method="POST" action="{{ route('adminpanel.automations.update', $rule) }}">@csrf<input type="hidden" name="enabled" value="{{ $rule->enabled ? 0 : 1 }}"><button class="v6-button v6-button--small {{ $rule->enabled ? 'v6-button--outline' : 'v6-button--primary' }}" type="submit">{{ $rule->enabled ? 'Szüneteltetés' : 'Bekapcsolás' }}</button></form>@if($rule->key === 'abandoned_cart' && $rule->enabled)<form method="POST" action="{{ route('adminpanel.automations.run', $rule) }}">@csrf<button class="v6-button v6-button--small v6-button--outline" type="submit">Futtatás</button></form>@endif</div></td></tr>
        @endforeach
        </tbody></table></div>
    </div>
    <div class="v6-subsection"><div class="v6-section__header"><div><span class="v6-eyebrow">FUTÁSI NAPLÓ</span><h2>Legutóbbi futások</h2><p>Az exportkatalógusban az automatizmusszabályok és futások külön adatkörként elérhetők.</p></div><a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.exports') }}">Exportok megnyitása</a></div><div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Időpont</th><th>Szabály</th><th>Címzett</th><th>Esemény</th><th>Állapot</th><th>Próbálkozás</th><th>Hiba</th></tr></thead><tbody>@forelse($recentRuns as $run)<tr><td>{{ $run->created_at?->format('Y-m-d H:i') }}</td><td>{{ $run->rule?->name ?: '—' }}</td><td>{{ $run->user?->email ?: '—' }}</td><td>{{ $run->event }}</td><td>{{ $run->status }}</td><td>{{ $run->attempts }}</td><td>{{ $run->last_error ?: '—' }}</td></tr>@empty<tr><td colspan="7">Még nincs automatizmus-futás.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
