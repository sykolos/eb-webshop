@php
    $imagePath = $product->image;
    $imageUrl = ($imagePath && Storage::disk('public')->exists($imagePath))
        ? Storage::disk('public')->url($imagePath)
        : null;
    if (!$imageUrl && $product->relationLoaded('media')) {
        $media = $product->media->first(fn ($item) => $item->kind === 'image' && $item->is_public && Storage::disk('public')->exists($item->path));
        $imageUrl = $media ? Storage::disk('public')->url($media->path) : null;
    }
    $productUrl = $product->slug ? route('product.slug', $product->slug) : route('product', $product->id);
    $unitPrice = auth()->check()
        ? ($product->pricing['final_price'] ?? $product->getPriceForUser())
        : 0;
@endphp
<article class="quick-list-item" data-price="{{ (float) $unitPrice }}" data-multiplier="{{ (float) ($product->product_unit->quantity ?? 1) }}">
    <a class="quick-list-item__image" href="{{ $productUrl }}"><img src="{{ $imageUrl ?: asset('img/landscape-placeholder.svg') }}" alt="{{ $product->title }}" loading="lazy"></a>
    <div class="quick-list-item__body">
        <a class="quick-list-item__title" href="{{ $productUrl }}">{{ str_replace(chr(160), ' ', $product->title) }}</a>
        <span class="quick-list-item__meta">{{ $product->serial_number }} · {{ \App\Models\Products::AVAILABILITY_STATUSES[$product->availability_status ?? 'in_stock'] ?? 'Elérhetőség egyeztetés alatt' }}</span>
        <p>{{ \Illuminate\Support\Str::limit($product->description, 150) }}</p>
        @if($product->product_unit)<span class="quick-list-item__unit">Kiszerelés: {{ $product->product_unit->unit }} ({{ $product->product_unit->quantity }}{{ $product->product_unit->measure }})</span>@endif
    </div>
    <div class="quick-list-item__controls">
        @auth
            @if($product->product_unit)
                <label for="quick-quantity-{{ $product->id }}">Mennyiség<input id="quick-quantity-{{ $product->id }}" class="quick-quantity" type="number" min="1" value="1" inputmode="numeric"></label>
                <strong class="calculated-price">{{ number_format((float) $unitPrice * (float) ($product->product_unit->quantity ?? 1), 0, ',', ' ') }} Ft</strong>
            @endif
            <button class="v6-button v6-button--primary add-to-cart" type="button" data-url="{{ route('addtocart', ['id' => $product->id, 'q' => $product->product_unit->quantity ?? 1, 'm' => $product->product_unit->measure ?? 'db']) }}" @disabled(($product->availability_status ?? 'in_stock') === 'out_of_stock')>Kosárba</button>
        @else
            <span class="quick-list-item__guest">Partnerár és vásárlás belépés után</span>
            <a class="v6-button v6-button--outline" href="{{ route('login', ['return_to' => request()->getRequestUri()]) }}">Belépés</a>
        @endauth
    </div>
</article>
