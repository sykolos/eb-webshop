@if($activeOffers->isNotEmpty())
<section class="m20-home-offers v6-section" aria-labelledby="active-offers-title">
    <div class="v6-section__header"><div><span class="v6-eyebrow">M20</span><h2 id="active-offers-title">Aktuális ajánlatok</h2><p>Kiemelt termékek és kategóriák, a meglévő partneri árképzés változatlanul hagyásával.</p></div><a class="v6-button v6-button--outline" href="{{ route('info.offers') }}">Összes ajánlat</a></div>
    <div class="m20-home-offers__list">@foreach($activeOffers->take(3) as $offer)<a href="{{ route('info.offers') }}#ajanlat-{{ $offer->slug }}"><strong>{{ $offer->title }}</strong><span>{{ $offer->summary ?: 'Ajánlat megnyitása' }}</span></a>@endforeach</div>
</section>
@endif
