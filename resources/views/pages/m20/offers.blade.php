@extends('layouts.v6.shop')

@section('title', 'Aktuális ajánlatok')
@section('content')
<div class="v6-container v6-m14-page">
    <div class="v6-section__header"><div><span class="v6-eyebrow">AKTUÁLIS AJÁNLATOK</span><h1>Aktuális ajánlatok</h1><p>A kiemelések tájékoztató tartalmak; a partneri árakat és kedvezményeket a meglévő árképzés számolja.</p></div></div>
    @forelse($activeOffers as $offer)
        @php($banner = $offer->banners->first(fn ($item) => $item->slot === 'hero' && $item->is_published))
        <article class="v6-section m20-offer" id="ajanlat-{{ $offer->slug }}">
            @if($banner && $banner->desktop_path)
                <a class="m20-offer__media" href="#ajanlat-{{ $offer->slug }}">
                    <picture>
                        @if($banner->mobile_path)<source media="(max-width: 520px)" srcset="{{ asset('storage/'.$banner->mobile_path) }}">@endif
                        @if($banner->tablet_path)<source media="(max-width: 1024px)" srcset="{{ asset('storage/'.$banner->tablet_path) }}">@endif
                        <img src="{{ asset('storage/'.$banner->desktop_path) }}" alt="{{ $banner->alt_text }}">
                    </picture>
                </a>
            @endif
            <div class="v6-section__header"><div><span class="v6-eyebrow">KIEMELT AJÁNLAT</span><h2>{{ $offer->title }}</h2>@if($offer->summary)<p>{{ $offer->summary }}</p>@endif</div></div>
            @if($offer->displayProducts->isNotEmpty())
                <div class="m20-offer__products">@foreach($offer->displayProducts as $product)<x-product-box :product="$product" />@endforeach</div>
            @else
                <div class="v6-empty-state"><strong>Ehhez az ajánlathoz jelenleg nincs elérhető termék.</strong><span>A kiemelés tartalma frissítés alatt áll.</span></div>
            @endif
        </article>
    @empty
        <section class="v6-section"><div class="v6-empty-state"><strong>Jelenleg nincs aktív ajánlat.</strong><span>Nézz vissza később, vagy böngészd a teljes webshopot.</span><a class="v6-button v6-button--primary" href="{{ route('orderpage') }}">Webshop megnyitása</a></div></section>
    @endforelse
</div>
@endsection
