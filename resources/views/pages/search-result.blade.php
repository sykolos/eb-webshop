@extends('layouts.public-v6')
@section('title', 'Keresési eredmények')
@section('content')
<section class="shop-v6-page">
    <header class="shop-v6-intro"><div class="v6-container"><nav class="shop-v6-breadcrumbs" aria-label="Morzsamenü"><a href="{{ route('home') }}">Főoldal</a><span aria-hidden="true">/</span><span aria-current="page">Keresés</span></nav><p class="v6-eyebrow">ElectroBusiness katalógus</p><h1>Keresési eredmények</h1><p>{{ $products->total() }} találat a „{{ request('query') }}” keresésre.</p></div></header>
    <div class="v6-container shop-v6-content">
        @if($products->count())
            <div class="shop-v6-product-grid">@foreach($products as $product)@include('components.home-v6.catalog-card', ['product' => $product])@endforeach</div>
            <nav class="shop-v6-pagination" aria-label="Keresési oldalak">{{ $products->appends(request()->query())->links() }}</nav>
        @else
            <div class="shop-v6-empty"><span class="shop-v6-empty__mark" aria-hidden="true">?</span><h2>Nincs találat</h2><p>Próbáljon másik kifejezést vagy cikkszámot.</p><a class="v6-button v6-button--outline" href="{{ route('orderpage') }}">Vissza a webshophoz</a></div>
        @endif
    </div>
</section>
@endsection
