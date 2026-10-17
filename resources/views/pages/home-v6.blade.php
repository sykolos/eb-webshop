@extends('layouts.home-v6')
@section('title', 'Villamossági alapanyagok szakembereknek')
@section('content')
@php($hero = $banners->active('hero'))
@php($floating = $banners->active('floating'))
@php($highlightedProducts = $highlighted->pluck('product')->filter()->take(4))
@php($newProducts = $latestProducts->take(4))

<section class="home-v6-hero">
    @if($hero)
        <picture class="home-v6-hero__image">
            <source media="(max-width: 520px)" srcset="{{ asset('storage/'.$hero->mobile_path) }}">
            <source media="(max-width: 1024px)" srcset="{{ asset('storage/'.$hero->tablet_path) }}">
            <img src="{{ asset('storage/'.$hero->desktop_path) }}" alt="{{ $hero->alt_text }}" width="1600" height="500">
        </picture>
    @else
        <img class="home-v6-hero__image" src="{{ asset('img/electrobusiness-hero.png') }}" alt="Villamossági termékek szakembereknek" width="1672" height="941">
    @endif
    <div class="home-v6-hero__veil"></div>
    <div class="v6-container home-v6-hero__inner">
        <div class="home-v6-hero__copy">
            <span class="v6-eyebrow">Courbi márkaképviselet</span>
            <h1>{{ $hero?->title ?: 'Biztos kapcsolat. Erős alapok.' }}</h1>
            <p>Villamossági alapanyagok szakembereknek, stabil elérhetőséggel és személyre szabott partnerárakkal.</p>
            <div class="home-v6-hero__actions">
                <a class="v6-button v6-button--primary" href="{{ route('orderpage') }}">Termékek böngészése <span aria-hidden="true">→</span></a>
                @guest
                    <a class="v6-button home-v6-button--ghost" href="{{ route('register') }}">Partner leszek</a>
                @else
                    <a class="v6-button home-v6-button--ghost" href="{{ route('account') }}">Fiókom</a>
                @endguest
            </div>
            <div class="home-v6-hero__trust"><span>✓ Nettó partnerárak</span><span>✓ 24–48 órás szállítás</span></div>
        </div>
        <aside class="home-v6-hero__note" aria-label="Főoldali kiemelés"><span>EB / 2026</span><strong>Szakmai<br>beszerzés<br>egyszerűbben.</strong><small>{{ $highlightedProducts->count() }} kiemelt termék</small></aside>
    </div>
</section>

@if($floating)
    <aside class="home-v6-floating-banner" aria-label="{{ $floating->alt_text }}">
        <a href="{{ $floating->link_url ?: ($floating->offer ? route('info.offers').'#ajanlat-'.$floating->offer->slug : route('orderpage')) }}">
            <picture><source media="(max-width: 520px)" srcset="{{ asset('storage/'.$floating->mobile_path) }}"><img src="{{ asset('storage/'.$floating->desktop_path) }}" alt="{{ $floating->alt_text }}" width="900" height="1125"></picture>
            @if($floating->title)<strong>{{ $floating->title }}</strong>@endif
        </a>
    </aside>
@endif

<section class="home-v6-benefits" aria-label="Szolgáltatási előnyök">
    <div class="v6-container home-v6-benefits__grid">
        <div><strong>Gyors kiszállítás</strong><span>Országosan 24–48 óra</span></div>
        <div><strong>Stabil elérhetőség</strong><span>Valós státuszok a katalógusban</span></div>
        <div><strong>Megbízható minőség</strong><span>Bevált gyártói háttér</span></div>
        <div><strong>Szakértői segítség</strong><span>Közvetlen kapcsolattartó</span></div>
    </div>
</section>

@if($activeOffers->isNotEmpty())
<section class="home-v6-section home-v6-section--white" aria-labelledby="home-offers-title">
    <div class="v6-container">
        <div class="home-v6-section-heading"><div><span class="v6-eyebrow">Electro Business</span><h2 id="home-offers-title">Aktuális ajánlatok</h2><p>Valós, időzített ajánlatok a meglévő partneri árképzés megtartásával.</p></div><a href="{{ route('info.offers') }}">Összes ajánlat →</a></div>
        <div class="home-v6-offers">@foreach($activeOffers->take(3) as $offer)<a href="{{ route('info.offers') }}#ajanlat-{{ $offer->slug }}"><span>Ajánlat</span><strong>{{ $offer->title }}</strong><small>{{ $offer->summary ?: 'Részletek megnyitása' }}</small></a>@endforeach</div>
    </div>
</section>
@endif

<section class="home-v6-section" aria-labelledby="home-categories-title">
    <div class="v6-container">
        <div class="home-v6-section-heading"><div><span class="v6-eyebrow">Gyors választás</span><h2 id="home-categories-title">Népszerű kategóriák</h2></div><a href="{{ route('orderpage') }}">Összes kategória →</a></div>
        @if($topCategories->isNotEmpty())
            <div class="home-v6-category-grid">
                @foreach($topCategories->take(4) as $category)
                    @php($categoryImage = $category->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($category->image) ? \Illuminate\Support\Facades\Storage::disk('public')->url($category->image) : asset('img/landscape-placeholder.svg'))
                    <a class="home-v6-category-card" href="{{ route('orderpage', ['category' => $category->id]) }}"><div><span>{{ (int) ($category->total ?? 0) }} termék</span><strong>{{ $category->name }}</strong><b>Megnézem →</b></div><img src="{{ $categoryImage }}" alt="" width="640" height="480" loading="lazy"></a>
                @endforeach
            </div>
        @else
            <div class="v6-empty-state"><strong>A kategóriák előkészítés alatt állnak.</strong><span>A teljes katalógus már böngészhető.</span><a class="v6-button v6-button--outline" href="{{ route('orderpage') }}">Webshop megnyitása</a></div>
        @endif
    </div>
</section>

<section class="home-v6-section home-v6-section--white" aria-labelledby="home-highlighted-title">
    <div class="v6-container">
        <div class="home-v6-section-heading"><div><span class="v6-eyebrow">Szakemberek választása</span><h2 id="home-highlighted-title">Kiemelt termékek</h2><p>A kiemelési sorrendet az adminfelület kezeli.</p></div><a href="{{ route('orderpage', ['category' => 'highlighted']) }}">Minden kiemelt termék →</a></div>
        @if($highlightedProducts->isNotEmpty())<div class="home-v6-product-grid">@foreach($highlightedProducts as $product) @include('components.home-v6.product-card', ['product' => $product]) @endforeach</div>@else<div class="v6-empty-state"><strong>Nincs kiemelt termék.</strong><span>Az adminfelületen beállítható kiemelések itt jelennek meg.</span></div>@endif
    </div>
</section>

<section class="home-v6-partner-strip"><div class="v6-container home-v6-partner-strip__inner"><div><span class="home-v6-eyebrow-light">Electro Partner</span><h2>Többet vásárolsz?<br><em>Jobb árat kapsz.</em></h2><p>Céges fiókkal egyedi nettó árakat, gyorsabb rendelést és szakmai támogatást kapsz.</p><a class="v6-button v6-button--primary" href="{{ route('register') }}">Partnerregisztráció →</a></div><div class="home-v6-partner-strip__metrics"><div><strong>24–48</strong><span>órás kiszállítás</span></div><div><strong>B2B</strong><span>szakmai beszerzés</span></div><div><strong>1:1</strong><span>szakmai segítség</span></div></div></div></section>

<section class="home-v6-section" aria-labelledby="home-latest-title">
    <div class="v6-container">
        <div class="home-v6-section-heading"><div><span class="v6-eyebrow">Az Electro Business kínálatából</span><h2 id="home-latest-title">Újdonságok és segítség a beszerzéshez</h2></div><a href="{{ route('contact') }}">Kérdezz tőlünk →</a></div>
        @if($newProducts->isNotEmpty())<div class="home-v6-new-product-grid">@foreach($newProducts as $product)<a href="{{ $product->slug ? route('product.slug', $product->slug) : route('product', $product->id) }}"><span>Új termék</span><strong>{{ $product->title }}</strong><small>{{ $product->serial_number }}</small><b>Megnyitás →</b></a>@endforeach</div>@else<div class="v6-empty-state"><strong>Nincs új termék az adatbázisban.</strong><span>A friss termékek a közzététel után itt jelennek meg.</span></div>@endif
        <div class="home-v6-support"><div><span class="home-v6-eyebrow-light">Offline katalógus</span><h3>Teljesebb kínálat, gyorsabb választás.</h3><p>A folyamatosan bővülő termékkínálatból segítünk megtalálni a megfelelő anyagot és kiszerelést.</p><a class="v6-button v6-button--primary" href="{{ route('info', ['slug' => 'katalogus']) }}">Katalógus és termékinformáció →</a></div><div><strong>Gyakori kérdések</strong><a href="{{ route('info', ['slug' => 'szallitas']) }}">Mennyi idő a szállítás? →</a><a href="{{ route('register') }}">Hogyan regisztráljak partnerként? →</a><a href="{{ route('info', ['slug' => 'fizetes']) }}">Milyen fizetési módok érhetők el? →</a></div></div>
    </div>
</section>
<div class="home-v6-cart-toast" data-home-cart-toast role="status" aria-live="polite" hidden></div>
@endsection
