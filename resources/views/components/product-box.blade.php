
@php
    $productUrl = $product->slug ? route('product.slug', $product->slug) : route('product', $product->id);
    $availability = $product->availability_status ?? 'in_stock';
@endphp
<a href="{{ $productUrl }}" class="product-box bg-dark">
	<div class="image bg-light">
		@php
            $imagePath = $product->image;
            $imageUrl = ($imagePath && Storage::disk('public')->exists($imagePath))
                ? Storage::disk('public')->url($imagePath)
                : null;
            if (!$imageUrl && $product->relationLoaded('media')) {
                $media = $product->media->first(function ($item) {
                    return $item->kind === 'image'
                        && $item->is_public
                        && Storage::disk('public')->exists($item->path);
                });
                $imageUrl = $media ? Storage::disk('public')->url($media->path) : null;
            }
        @endphp
		<img class="img-fluid w-100"
			src="{{ $imageUrl ?: asset('img/landscape-placeholder.svg') }}"
			alt="{{ $product->title }}"
			width="100" height="100"
			loading="lazy">
	</div>

	<div class="product-info">
		<div class="product-title">
			{{ $product->title }}
		</div>

		<div class="product-meta mt-auto">
			<div class="product-serialnum small text-light">
				{{ $product->serial_number }}
			</div>
			<div class="availability-badge availability-{{ $availability }}">
				{{ \App\Models\Products::AVAILABILITY_STATUSES[$availability] ?? 'Elérhetőség egyeztetés alatt' }}
			</div>
			<div class="product-price">
				@auth
					{{ $product->pricing['final_price'] ?? $product->getPriceForUser() }} Ft
					@if($product->product_unit)
						/ {{ $product->product_unit->measure }}
					@endif
			@else
				<span class="text-light small fw-light">Partnerár belépés után</span>
				@endauth
			</div>
		</div>
	</div>
</a>

