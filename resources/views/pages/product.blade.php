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

<div class="product-page">
    <div class="container">
        <div class="product-page-row">
            <section class="product-page-image">
                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" loading="lazy">
            </section>
            <section class="product-page-details">
                <p class="p-title">
                    {{ $product->title }}
                </p>                
                <p class="p-price">
                    {{ $product->getPriceForUser() }} Ft 
                    <span class="p-unit"> + Áfa / {{ $product->product_unit->measure }}</span>
                </p>
                <p class="item-number">
                    Cikkszám: {{ $product->serial_number }}
                </p>
                <p class="p-category">
                    Kategória: {{ $product->category->name }}
                </p>
                <p class="p-description">
                    Leírás: {{ $product->description }}
                </p>
                <hr>
                <form action="{{ route('addtocart', ['id' => $product->id, 'q' => $unit->quantity, 'm' => $unit->measure]) }}" method="post">
                    @csrf
                    <div class="p-form">
                        <div class="p-color">
                            <label for="color">Kiszerelés:</label>
                            {{ $unit->unit }}
                            ({{ $unit->quantity }}{{ $unit->measure }})
                        </div>
                        <div class="p-quantity">                                 
                            <label for="quantity">Mennyiség:</label>
                            <input id="quantity" type="number" name="quantity" min="1" max="100" value="1" required>
                            <span> x {{ $unit->quantity }}{{ $unit->measure }}</span>

                            <div class="p-calculate pt-3">
                                <p>Összesen:
                                    <span class="p-price" id="calc-price"></span><span class="p-unit"> Ft + Áfa</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cart">Kosárba</button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
