@extends('layouts.v6.admin')

@section('title', 'Aktuális ajánlatok')
@section('heading', 'Aktuális ajánlatok')
@section('intro', 'Kézzel kiválasztott termékek és kategóriák időzített, partnerárat nem módosító kiemelése.')

@section('content')
<div class="v6-section">
    @if(session('success'))<div class="v6-toast" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif
    <div class="v6-notice">Az ajánlat csak tartalmi kiemelés. Az árakat, kedvezményeket, sorrendet és kerekítést továbbra is a meglévő PricingService adja.</div>

    <form method="POST" action="{{ route('adminpanel.offers.store') }}" class="v6-form">
        @csrf
        <div class="v6-section__header"><div><span class="v6-eyebrow">M20.01–M20.02</span><h2>Új ajánlat</h2><p>Terméket és/vagy teljes kategóriaágat válassz ki. Üres kapcsolatokkal az oldal üres állapotot mutat.</p></div></div>
        @include('admin.m20.offers._form-fields', ['offer' => null])
        <div class="v6-actions"><button class="v6-button v6-button--primary" type="submit">Ajánlat mentése</button></div>
    </form>

    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">Publikációs lista</span><h2>Mentett ajánlatok</h2></div></div>
        <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Ajánlat</th><th>Kapcsolatok</th><th>Időszak</th><th>Állapot</th><th>Műveletek</th></tr></thead><tbody>
        @forelse($offers as $offer)
            <tr><td><strong>{{ $offer->title }}</strong><small>{{ $offer->slug }}</small></td><td>{{ $offer->products_count }} termék · {{ $offer->categories_count }} kategória</td><td>{{ $offer->starts_at?->format('Y-m-d H:i') ?: 'Azonnal' }} – {{ $offer->ends_at?->format('Y-m-d H:i') ?: 'Visszavonásig' }}</td><td><span class="v6-status {{ $offer->is_published ? 'v6-status--success' : 'v6-status--neutral' }}">{{ $offer->is_published ? 'Publikus' : 'Piszkozat' }}</span></td><td><div class="v6-actions"><a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.offers.edit', $offer) }}">Szerkesztés</a>@if($offer->is_published)<form method="POST" action="{{ route('adminpanel.offers.archive', $offer) }}">@csrf<button class="v6-button v6-button--small v6-button--outline" type="submit">Visszavonás</button></form>@else<form method="POST" action="{{ route('adminpanel.offers.publish', $offer) }}">@csrf<button class="v6-button v6-button--small v6-button--primary" type="submit">Publikálás</button></form>@endif</div></td></tr>
        @empty
            <tr><td colspan="5"><div class="v6-empty"><strong>Még nincs ajánlat</strong><span>Az első mentés után itt kezelheted az időzített kiemeléseket.</span></div></td></tr>
        @endforelse
        </tbody></table></div>
        {{ $offers->links() }}
    </div>
</div>
@endsection
