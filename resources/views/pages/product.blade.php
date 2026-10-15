@extends('layouts.master')
@section('title', $product->title)

@section('content')
@php
    $availability = $product->availability_status ?? 'in_stock';
    $availabilityLabel = \App\Models\Products::AVAILABILITY_STATUSES[$availability] ?? 'Elérhetőség egyeztetés alatt';
    $productUrl = $product->slug ? route('product.slug', $product->slug) : route('product', $product->id);
    $publicImages = $product->media->where('kind', 'image')->where('is_public', true);
    $documents = $product->media->where('kind', 'document');
@endphp

@if (session()->has('addedtocart'))
    <div class="container pt-3"><div class="alert alert-success" role="status">{{ session()->get('addedtocart') }}</div></div>
@endif

<main class="product-page">
    <div class="container py-4 py-lg-5">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <nav aria-label="Morzsamenü">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Főoldal</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('orderpage') }}">Webshop</a></li>
                    @if($product->category)
                        <li class="breadcrumb-item"><a href="{{ route('orderpage', ['category' => $product->category->id]) }}">{{ $product->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->title }}</li>
                </ol>
            </nav>
            <a href="{{ $product->category ? route('orderpage', ['category' => $product->category->id]) : route('orderpage') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Vissza a termékekhez
            </a>
        </div>

        <article class="product-detail">
            <div class="row g-4 g-xl-5">
                <div class="col-lg-6">
                    <div class="product-gallery">
                        @php
                            $imagePath = $product->image;
                            $imageUrl = ($imagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath))
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath)
                                : asset('img/landscape-placeholder.svg');
                        @endphp
                        <img src="{{ $imageUrl }}" alt="{{ $product->title }}" class="product-gallery__main" loading="eager">
                        @if($publicImages->count() > 0)
                            <div class="product-gallery__thumbs" aria-label="Termékképek">
                                @foreach($publicImages as $media)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($media->path) }}" alt="{{ $media->title ?: $product->title }}" loading="lazy">
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    <p class="eyebrow mb-2">{{ $product->manufacturer ?: 'Courbi termék' }}</p>
                    <h1 class="product-detail__title">{{ $product->title }}</h1>
                    <p class="text-muted mb-3">Cikkszám: <strong>{{ $product->serial_number }}</strong></p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="availability-badge availability-{{ $availability }}">{{ $availabilityLabel }}</span>
                        @if($unit)
                            <span class="badge text-bg-light border">{{ $unit->unit }} · {{ $unit->quantity }}{{ $unit->measure }}</span>
                        @endif
                    </div>

                    @if($product->description)
                        <div class="product-description mb-4">{!! nl2br(e($product->description)) !!}</div>
                    @endif

                    @auth
                        <div class="product-purchase-panel">
                            <div class="product-price-large">
                                {{ $product->pricing['final_price'] ?? $product->getPriceForUser() }} Ft
                                @if($unit)<span>+ Áfa / {{ $unit->measure }}</span>@endif
                            </div>
                            <form action="{{ route('addtocart', ['id' => $product->id, 'q' => $unit->quantity ?? 1, 'm' => $unit->measure ?? 'db']) }}" method="post">
                                @csrf
                                @if($unit)
                                    <label class="form-label" for="quantity">Mennyiség</label>
                                    <div class="input-group mb-3">
                                        <input id="quantity" type="number" name="quantity" class="form-control" min="1" max="100" value="1" required>
                                        <span class="input-group-text">x {{ $unit->quantity }}{{ $unit->measure }}</span>
                                    </div>
                                @endif
                                <button type="submit" class="btn btn-primary btn-lg" @disabled($availability === 'out_of_stock')>
                                    <i class="bi bi-cart-plus" aria-hidden="true"></i> Kosárba
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="partner-gate p-4">
                            <h2 class="h5">Partnerár és vásárlás belépés után</h2>
                            <p class="text-muted mb-3">A katalógus szabadon böngészhető. A személyre szabott árak és a kosár használata partnerfiókhoz kötött.</p>
                            <a class="btn btn-dark" href="{{ route('login') }}">Partner belépés</a>
                        </div>
                    @endauth
                </div>
            </div>

            <div class="row g-4 mt-4 mt-lg-5">
                @if(is_array($product->specifications) && count($product->specifications))
                    <div class="col-lg-7">
                        <section class="product-detail-section">
                            <h2 class="h4">Műszaki adatok</h2>
                            <dl class="specification-list mb-0">
                                @foreach($product->specifications as $key => $value)
                                    <div><dt>{{ is_string($key) ? $key : 'Adat' }}</dt><dd>{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd></div>
                                @endforeach
                            </dl>
                        </section>
                    </div>
                @endif

                <div class="col-lg-5">
                    <section class="product-detail-section">
                        <h2 class="h4">Dokumentumok</h2>
                        @if($documents->count())
                            <ul class="list-unstyled document-list mb-0">
                                @foreach($documents as $document)
                                    @auth
                                        <li><a href="{{ route('product.media.download', [$product->id, $document->id]) }}"><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> {{ $document->title ?: 'Termékdokumentum' }}</a></li>
                                    @else
                                        <li class="text-muted">A dokumentumok partner belépés után tölthetők le.</li>
                                    @endauth
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">Ehhez a termékhez jelenleg nincs feltöltött dokumentum.</p>
                        @endif
                    </section>
                </div>
            </div>
        </article>

        @if($similar->count())
            <section class="recommended-products-section mt-5" aria-labelledby="similar-products-title">
                <h2 id="similar-products-title" class="h3 mb-4">Hasonló termékek</h2>
                <div class="swiper recommended-swiper">
                    <div class="swiper-wrapper">
                        @foreach($similar as $similarProduct)
                            <div class="swiper-slide"><x-product-box :product="$similarProduct" /></div>
                        @endforeach
                    </div>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </section>
        @endif
    </div>
</main>

@if($similar->count())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new Swiper('.recommended-swiper', {
                slidesPerView: 4,
                spaceBetween: 20,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    320: { slidesPerView: 1.2 },
                    576: { slidesPerView: 2.2 },
                    768: { slidesPerView: 3 },
                    992: { slidesPerView: 4 },
                },
            });
        });
    </script>
@endif

@endsection
