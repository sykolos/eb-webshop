@extends('layouts.master')
@section('title','Főoldal')
@section('content')
<main class="homepage flex-shrink-0">
    <!-- Header-->
    @include('pages.components.home.header')
    {{-- Swiper section --}}
    @include('pages.components.home.product-swipers', [
    'highlighted' => $highlighted,
    'latestProducts' => $latestProducts,
    'topCategories' => $topCategories
])
    {{-- Why us & cta section --}}
    @include('pages.components.home.whyus-cta')
    {{-- Catalog section --}}
    @include('pages.components.home.catalog')
    
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new Swiper('.highlighted-swiper', {
        slidesPerView: 4,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 2000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.highlighted-next',
            prevEl: '.highlighted-prev',
        },
        breakpoints: {
            0: { slidesPerView: 1 },
            375: { slidesPerView: 1.2 },
            420: { slidesPerView: 1.4 },
            480: { slidesPerView: 1.6 },
            576: { slidesPerView: 2 },
            768: { slidesPerView: 3 },
            992: { slidesPerView: 4 },
            1200: { slidesPerView: 5 },
        }
    });

    new Swiper('.top-categories-swiper', {
        slidesPerView: 4,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.topcats-next',
            prevEl: '.topcats-prev',
        },
        breakpoints: {
            0: { slidesPerView: 1 },
            375: { slidesPerView: 1.2 },
            420: { slidesPerView: 1.4 },
            480: { slidesPerView: 1.6 },
            576: { slidesPerView: 2 },
            768: { slidesPerView: 3 },
            992: { slidesPerView: 4 },
            1200: { slidesPerView: 5 },
        }
    });

    new Swiper('.latest-products-swiper', {
        slidesPerView: 4,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 2800,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.latest-next',
            prevEl: '.latest-prev',
        },
        breakpoints: {
            0: { slidesPerView: 1 },
            375: { slidesPerView: 1.2 },
            420: { slidesPerView: 1.4 },
            480: { slidesPerView: 1.6 },
            576: { slidesPerView: 2 },
            768: { slidesPerView: 3 },
            992: { slidesPerView: 4 },
            1200: { slidesPerView: 5 },
        }
    });
});
</script>
@endsection