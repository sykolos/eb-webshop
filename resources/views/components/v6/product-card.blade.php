@php($productImage = data_get($product, 'image'))
<article class="v6-product-card">
    <div class="v6-product-card__media">
        @if($productImage)
            <img src="{{ asset('storage/' . ltrim($productImage, '/')) }}" alt="{{ data_get($product, 'title', 'Termék') }}">
        @else
            <span class="v6-product-card__placeholder">Termékkép előnézet</span>
        @endif
    </div>
    <div class="v6-product-card__body">
        <span class="v6-product-card__meta">{{ data_get($product, 'serial_number', 'EB-PREVIEW') }}</span>
        <h3>{{ data_get($product, 'title', 'Ipari kapcsoló és szerelési tartozék') }}</h3>
        <span class="v6-status v6-status--success">Készleten</span>
        <div class="v6-product-card__bottom">
            <span class="v6-price">{{ data_get($product, 'price', 'Partnerár után') }}</span>
            <a class="v6-button v6-button--primary v6-button--small" href="{{ route('login') }}">Részletek</a>
        </div>
    </div>
</article>
