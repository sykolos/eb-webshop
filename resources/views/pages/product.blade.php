@extends('layouts.public-v6')
@section('title', $product->title)

@section('content')
@php
    $availability = $product->availability_status ?? 'in_stock';
    $availabilityLabel = \App\Models\Products::AVAILABILITY_STATUSES[$availability] ?? 'Elérhetőség egyeztetés alatt';
    $productUrl = $product->slug ? route('product.slug', $product->slug) : route('product', $product->id);
    $publicImages = $product->media->where('kind', 'image')->where('is_public', true);
    $documents = $product->media->where('kind', 'document');
    $imagePath = $product->image;
    $imageUrl = ($imagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath))
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath)
        : asset('img/landscape-placeholder.svg');
@endphp

<section class="product-v6-page">
    <div class="v6-container product-v6-content">
        <nav class="shop-v6-breadcrumbs" aria-label="Morzsamenü">
            <a href="{{ route('home') }}">Főoldal</a><span aria-hidden="true">/</span>
            <a href="{{ route('orderpage') }}">Webshop</a>
            @if($product->category)<span aria-hidden="true">/</span><a href="{{ route('orderpage', ['category' => $product->category->id]) }}">{{ $product->category->name }}</a>@endif
            <span aria-hidden="true">/</span><span aria-current="page">{{ $product->title }}</span>
        </nav>

        @if(session()->has('addedtocart'))
            <div class="shop-v6-alert" role="status">{{ session()->get('addedtocart') }}</div>
        @endif

        <article class="product-v6-detail">
            <div class="product-v6-gallery">
                <div class="product-v6-gallery__main"><img src="{{ $imageUrl }}" alt="{{ $product->title }}" loading="eager"></div>
                @if($publicImages->count() > 0)
                    <div class="product-v6-gallery__thumbs" aria-label="Termékképek">
                        @foreach($publicImages as $media)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($media->path) }}" alt="{{ $media->title ?: $product->title }}" loading="lazy">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="product-v6-summary">
                <p class="v6-eyebrow">{{ $product->manufacturer ?: 'ElectroBusiness termék' }}</p>
                <h1>{{ $product->title }}</h1>
                <p class="product-v6-serial">Cikkszám: <strong>{{ $product->serial_number }}</strong></p>
                <div class="product-v6-badges">
                    <span class="v6-status {{ match($availability) { 'in_stock' => 'v6-status--success', 'supplier_stock', 'procurement' => 'v6-status--warning', 'out_of_stock' => 'v6-status--danger', default => 'v6-status--neutral' } }}">{{ $availabilityLabel }}</span>
                    @if($unit)<span class="product-v6-unit">{{ $unit->unit }} · {{ $unit->quantity }}{{ $unit->measure }}</span>@endif
                </div>
                @if($product->description)<div class="product-v6-description">{!! nl2br(e($product->description)) !!}</div>@endif

                @auth
                    <div class="product-v6-purchase">
                        <div class="product-v6-price">{{ number_format((float) ($product->pricing['final_price'] ?? $product->getPriceForUser()), 0, ',', ' ') }} Ft @if($unit)<span>/ {{ $unit->measure }}</span>@endif</div>
                        <form action="{{ route('addtocart', ['id' => $product->id, 'q' => $unit->quantity ?? 1, 'm' => $unit->measure ?? 'db']) }}" method="post" class="product-v6-purchase__form">
                            @csrf
                            @if($unit)
                                <label for="quantity">Mennyiség</label>
                                <div class="product-v6-quantity"><input id="quantity" type="number" name="quantity" min="1" max="100" value="1" required><span>x {{ $unit->quantity }}{{ $unit->measure }}</span></div>
                            @endif
                            <button type="submit" class="v6-button v6-button--primary" @disabled($availability === 'out_of_stock')>Kosárba</button>
                        </form>
                        <form action="{{ route('account.favorite.store', $product->id) }}" method="post">
                            @csrf
                            <button type="submit" class="v6-button v6-button--outline v6-button--small">♡ Kedvencekhez adás / eltávolítás</button>
                        </form>
                    </div>
                @else
                    <div class="product-v6-gate"><h2>Partnerár és vásárlás belépés után</h2><p>A katalógus szabadon böngészhető. A személyre szabott árak és a kosár használata partnerfiókhoz kötött.</p><a class="v6-button v6-button--dark" href="{{ route('login') }}">Partner belépés</a></div>
                @endauth
            </div>
        </article>

        <div class="product-v6-info-grid">
            @if(is_array($product->specifications) && count($product->specifications))
                <section class="product-v6-panel"><h2>Műszaki adatok</h2><dl class="product-v6-specs">@foreach($product->specifications as $key => $value)<div><dt>{{ is_string($key) ? $key : 'Adat' }}</dt><dd>{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd></div>@endforeach</dl></section>
            @endif
            <section class="product-v6-panel"><h2>Dokumentumok</h2>
                @if($documents->count())
                    <ul class="product-v6-documents">@foreach($documents as $document)<li>@auth<a href="{{ route('product.media.download', [$product->id, $document->id]) }}">Letöltés: {{ $document->title ?: 'Termékdokumentum' }}</a>@else<span>A dokumentumok partner belépés után tölthetők le.</span>@endauth</li>@endforeach</ul>
                @else<p class="product-v6-muted">Ehhez a termékhez jelenleg nincs feltöltött dokumentum.</p>@endif
            </section>
        </div>

        @if($similar->count())
            <section class="product-v6-recommended" aria-labelledby="similar-products-title"><div class="home-v6-section-heading"><div><p class="v6-eyebrow">Kapcsolódó termékek</p><h2 id="similar-products-title">Hasonló termékek</h2></div><a href="{{ $product->category ? route('orderpage', ['category' => $product->category->id]) : route('orderpage') }}">Vissza a katalógushoz</a></div><div class="shop-v6-product-grid">@foreach($similar as $similarProduct)@include('components.home-v6.catalog-card', ['product' => $similarProduct])@endforeach</div></section>
        @endif
    </div>
</section>
@endsection
