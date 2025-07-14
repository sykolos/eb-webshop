@extends('layouts.master')
@section('title', $product->title)

@section('content')

<script>
    $(document).ready(function(){
        let q = 1;
        const t = {{ $unit->quantity }};
        const p = {{ $product->getPriceForUser() }};
        const updatePrice = () => {
            const quantity = $('#quantity').val();
            const total = quantity * t * p;
            $('#calc-price').text(total);
        };
        updatePrice();
        $('#quantity').on('input', updatePrice);
    });
</script>

@if (session()->has('addedtocart'))
<section class="pop-up">
    <div class="pop-up-box">
        <div class="pop-up-title">
            {{ session()->get('addedtocart') }}
        </div>
        <div class="pop-up-actions">
            <a href="{{ route('orderpage') }}" class="btn btn-outlined">Tovább vásárlok</a>
            <a href="{{ route('cart') }}" class="btn btn-primary">Irány a kosár</a>
        </div>
    </div>
</section>
@endif

<div class="container py-5 product-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Főoldal</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orderpage') }}">Termékek</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->title }}</li>
            </ol>
        </nav>
        <a href="{{ route('orderpage', ['category_id' => $product->category->id]) }}" class="btn btn-outline-secondary btn-sm">
            &larr; Vissza a termékekhez
        </a>
    </div>

    <div class="card mb-5 bg-dark product-card">
        <div class="row g-4">
            <div class="col-md-5 img-col d-flex align-items-center justify-content-center">
                @php
                    $imagePath = $product->image;
                @endphp

                <img src="{{ Storage::exists($imagePath) ? Storage::url($imagePath) : asset('img/landscape-placeholder.svg') }}" 
                    alt="{{ $product->title }}" 
                    class="img-fluid" 
                    loading="lazy">
            </div>
            <div class="col-md-7 p-4">
                <h2 class="mb-2">{{ $product->title }}</h2>
                <p class="text-light mb-1">Cikkszám: {{ $product->serial_number }}</p>
                <p class="text-light mb-1">Kategória: {{ $product->category->name }}</p>
                <p class="lead fw-bold">
                    {{ $product->getPriceForUser() }} Ft 
                    <span class="text-light">+ Áfa / {{ $product->product_unit->measure }}</span>
                </p>

                <p class="mt-3 mb-4">
                    <strong>Leírás:</strong><br>
                    {{ $product->description }}
                </p>

                <hr>

                <form action="{{ route('addtocart', ['id' => $product->id, 'q' => $unit->quantity, 'm' => $unit->measure]) }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Kiszerelés:</label>
                            {{ $unit->unit }} ({{ $unit->quantity }}{{ $unit->measure }})
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Mennyiség:</label>
                        <div class="input-group">
                            <input id="quantity" type="number" name="quantity" class="form-control quantity-input" min="1" max="100" value="1" required>
                            <span class="input-group-text">x {{ $unit->quantity }}{{ $unit->measure }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="fw-semibold">Összesen: 
                            <span class="fw-bold calculated-price" id="calc-price"></span>
                            <span class="text-light">Ft + Áfa</span>
                        </p>
                    </div>

                    <button type="submit" class="btn btn-cart">
                        Kosárba
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Ajánlott termékek --}}
    @if($similar->count())
    <div class="my-5 position-relative">
        <h3 class="mb-4">Ajánlott termékek</h3>
        <div class="swiper recommended-swiper">
            <div class="swiper-wrapper">
                @foreach($similar as $product)
                    <div class="swiper-slide">
                        <x-product-box :product="$product" />
                    </div>
                @endforeach
            </div>
        </div>

        <div class="swiper-button-prev position-absolute"></div>
        <div class="swiper-button-next position-absolute"></div>
    </div>
    @endif


</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
	new Swiper('.recommended-swiper', {
		slidesPerView: 4,
		spaceBetween: 20,
		loop: true,
		autoplay: {
			delay: 2500,
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
