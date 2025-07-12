@extends('layouts.master')
@section('title','Főoldal')
@section('content')
<main class="homepage flex-shrink-0">
    
    
    
    <!-- Header-->
    @include('pages.components.home.header')
    <!-- Recommended Product section-->
    <section class="py-5" id="recommended-products">
        @if($highlighted->count())
            @php
                $validHighlighted = $highlighted->filter(fn($item) => $item->product !== null);
            @endphp

            @if($validHighlighted->count())
            <div class="my-5 position-relative">
                <h3 class="mb-4">Kiemelt termékeink</h3>
                <div class="swiper highlighted-swiper">
                    <div class="swiper-wrapper">
                        @foreach($validHighlighted as $item)
                            <div class="swiper-slide">
                                <x-product-box :product="$item->product" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="swiper-button-prev position-absolute"></div>
                <div class="swiper-button-next position-absolute"></div>
            </div>
            @endif
        @endif
    </section>  

    <!-- Features section-->
    <section class="py-5" id="features">
        <div class="container-fluid px-5 my-5">
            <div class="row gx-5">
                <div class="col-lg-4 mb-5 mb-lg-0"><h2 class="mb-0 section-header">Miért éri meg a partnerünknek lenni?</h2></div>
                <div class="col-lg-8">
                    <div class="row gx-5 row-cols-1 row-cols-md-2">
                        <div class="col mb-5 h-100">
                            <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-collection"></i></div>
                            <h2 class="h5">Gyors Szállítás</h2>
                            <p class="mb-0">A legtöbb esetben szállításunk országosan mindössze 24-48 órán belül teljesítjük.</p>
                        </div>
                        <div class="col mb-5 h-100">
                            <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-building"></i></div>
                            <h2 class="h5">Kedvezmények</h2>
                            <p class="mb-0">Legyen partnerünk és élvezze a különleges árakat, melyeket partnereink számára kínálunk.</p>
                        </div>
                        <div class="col mb-5 mb-md-0 h-100">
                            <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-toggles2"></i></div>
                            <h2 class="h5">Dinamikus</h2>
                            <p class="mb-0">Raktárkészletünk folyamatosan frissül és bővül, hogy mindig a legújabb termékeket kínálhassuk Önnek.</p>
                        </div>
                        <div class="col h-100">
                            <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-arrows-move"></i></div>
                            <h2 class="h5">Rugalmasság</h2>
                            <p class="mb-0">Széles választékú termékkínálatunk az ügyfeleink igényeire szabtuk.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Testimonial section-->
    <section class="py-5 bg-dark text-white">
        <div class="container-fluid px-5 my-5">
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-10 col-xl-7">
                    <div class="text-center">
                        <div class="fs-4 mb-4 fst-italic">Vedd fel velünk a kapcsolatot a verhetetlen árainkért</div>
                        <div class="d-flex align-items-center justify-content-center">
                            
                        <a class="btn btn-primary btn-lg px-4 me-sm-3 bg-gradient" href="#features">Kapcsolat</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-5" id="scroll-target">
        <div class="container-fluid px-5 my-5">
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6"><img class="img-fluid rounded mb-5 mb-lg-0" src="{{asset('img/cataloglogo.png')}}" loading="lazy" alt="..." /></div>
                <div class="col-lg-6">
                    <h2 class="section-header">Tekintse meg offline katalógusunkat</h2>
                    <p class="lead fw-normal text-muted mb-0">Katalógusunk az állandóan raktáron tartott és folyamatosan forgalmazott termékeinket tartalmazza.</p>
                    <a class="btn btn-outlined btn-lg px-4 my-3 me-sm-3 bg-gradient" href="https://electrobusiness.hu/A4_katalogus_electro_business_web.pdf">Letöltés</a>
                        
                </div>
            </div>
        </div>
    </section>
    
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
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                320: { slidesPerView: 1.2 },
                576: { slidesPerView: 2.2 },
                768: { slidesPerView: 3 },
                992: { slidesPerView: 4 },
                1200: { slidesPerView: 5 },
            }
        });
    });
</script>

@endsection