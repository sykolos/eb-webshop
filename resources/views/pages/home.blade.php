@extends('layouts.master')
@section('title','Főoldal')
@section('content')
<main class="homepage flex-shrink-0">
    @include('pages.components.home.banner', ['banners' => $banners])
    @include('pages.components.home.floating-banner', ['banners' => $banners])
    @include('pages.components.home.offers', ['activeOffers' => $activeOffers])
    <!-- Header-->
    @include('pages.components.home.header')
    {{-- Native horizontal product scrollers --}}
    @include('pages.components.home.product-scrollers', [
    'highlighted' => $highlighted,
    'latestProducts' => $latestProducts,
    'topCategories' => $topCategories
])
    {{-- Why us & cta section --}}
    @include('pages.components.home.whyus-cta')
    {{-- Catalog section --}}
    @include('pages.components.home.catalog')
    
</main>

@endsection
