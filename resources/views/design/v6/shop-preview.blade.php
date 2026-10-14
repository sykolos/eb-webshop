@extends('layouts.v6.shop')
@section('title', 'Webshop layout preview')
@section('content')
<section class="v6-hero">
    <div class="v6-container"><div class="v6-hero__content">
        <p class="v6-eyebrow">Védett staging preview</p>
        <h1>Megbízható ipari alapok a következő munkához.</h1>
        <p>Az új Electro Business felületi irány a gyors katalógusra, egyértelmű állapotokra és partnerközpontú vásárlásra épül.</p>
        <div class="v6-hero__actions"><a class="v6-button v6-button--primary" href="#products">Termékek megtekintése</a><a class="v6-button v6-button--outline" href="{{ route('adminpanel.design-system') }}">Komponensgaléria</a></div>
    </div></div>
</section>
<section class="v6-section v6-section--white">
    <div class="v6-container">
        <div class="v6-section-heading"><div><h2>Kategóriák</h2><p>A tartalom első pillantásra szkennelhető, a mélyebb kategóriafa későbbi mérföldkőben kapcsolódik.</p></div></div>
        <div class="v6-category-grid"><a class="v6-category-tile" href="#products"><strong>Kapcsolók</strong><span>Ipari és lakossági megoldások</span></a><a class="v6-category-tile" href="#products"><strong>Kábelek</strong><span>Vezetékek, csatlakozók</span></a><a class="v6-category-tile" href="#products"><strong>Védelem</strong><span>Kismegszakítók és tartozékok</span></a><a class="v6-category-tile" href="#products"><strong>Szerelés</strong><span>Gyorsan hozzáadható kellékek</span></a></div>
    </div>
</section>
<section id="products" class="v6-section">
    <div class="v6-container"><div class="v6-section-heading"><div><h2>Kiemelt termékek</h2><p>Publikus previewban nincs partnerár; a valódi ár továbbra is backendből és jogosultságból érkezik.</p></div><a href="{{ route('adminpanel.design-system') }}">Állapotok</a></div><div class="v6-product-grid">@foreach($products as $product) @include('components.v6.product-card', ['product' => $product]) @endforeach</div></div>
</section>
<section class="v6-section v6-section--white"><div class="v6-container"><div class="v6-empty-state"><strong>Üres, hiba- és töltési állapot</strong><span>A komponenseknek van helye akkor is, amikor nincs találat vagy folyamatban van a kérés.</span></div></div></section>
@endsection
