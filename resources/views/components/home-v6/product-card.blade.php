@php
    $productUrl = $product->slug ? route('product.slug', $product->slug) : route('product', $product->id);
    $availability = $product->availability_status ?? 'in_stock';
    $availabilityLabel = \App\Models\Products::AVAILABILITY_STATUSES[$availability] ?? 'Elérhetőség egyeztetés alatt';
    $imageUrl = null;
    if ($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
        $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($product->image);
    }
    if (!$imageUrl && $product->relationLoaded('media')) {
        $media = $product->media->first(fn ($item) => $item->kind === 'image' && $item->is_public && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->path));
        $imageUrl = $media ? \Illuminate\Support\Facades\Storage::disk('public')->url($media->path) : null;
    }
    $statusClass = match ($availability) {
        'in_stock' => 'v6-status--success',
        'supplier_stock', 'procurement' => 'v6-status--warning',
        'out_of_stock' => 'v6-status--danger',
        default => 'v6-status--neutral',
    };
    $price = auth()->check() ? ($product->pricing['final_price'] ?? $product->getPriceForUser()) : null;
@endphp
<article class="home-v6-product-card">
    <div class="home-v6-product-card__media">
        <a href="{{ $productUrl }}" aria-label="{{ $product->title }} megnyitása">
            <img src="{{ $imageUrl ?: asset('img/landscape-placeholder.svg') }}" alt="{{ $product->title }}" width="640" height="480" loading="lazy">
        </a>
        @auth
            <form method="post" action="{{ route('account.favorite.store', $product->id) }}" class="home-v6-product-card__favorite">
                @csrf
                <button type="submit" aria-label="{{ $product->title }} mentése a kedvencek közé">♡</button>
            </form>
        @endauth
    </div>
    <div class="home-v6-product-card__body">
        <span class="home-v6-product-card__category">{{ $product->category?->name ?? 'Villamossági termék' }}</span>
        <a href="{{ $productUrl }}"><h3>{{ $product->title }}</h3></a>
        <span class="home-v6-product-card__meta">Cikkszám: {{ $product->serial_number ?: '—' }}</span>
        <div class="home-v6-product-card__status">
            <span class="v6-status {{ $statusClass }}">{{ $availabilityLabel }}</span>
            @if($product->product_unit)<span>{{ $product->product_unit->measure }}</span>@endif
        </div>
        <div class="home-v6-product-card__bottom">
            <div>
                @auth
                    <strong class="v6-price">{{ number_format((float) $price, 0, ',', ' ') }} Ft</strong>
                    @if($product->product_unit)<span class="home-v6-product-card__unit">/ {{ $product->product_unit->measure }}</span>@endif
                @else
                    <span class="v6-price v6-price--muted">Partnerár belépés után</span>
                @endauth
            </div>
            @auth
                @if($availability !== 'out_of_stock')
                    <form method="post" action="{{ route('addtocart', [$product->id, $product->product_unit?->quantity ?? 1, $product->product_unit?->measure ?? 'db']) }}" data-home-cart-form>
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button class="v6-button v6-button--dark v6-button--small" type="submit">Kosárba</button>
                    </form>
                @else
                    <span class="home-v6-product-card__unavailable">Nem rendelhető</span>
                @endif
            @else
                <a class="v6-button v6-button--outline v6-button--small" href="{{ route('login') }}">Belépés</a>
            @endauth
        </div>
    </div>
</article>
