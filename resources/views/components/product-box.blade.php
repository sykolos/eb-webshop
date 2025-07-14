
<a href="{{ route('product', $product->id) }}" class="product-box bg-dark">
	<div class="image bg-light">
		@php
            $imagePath = $product->image;
        @endphp
		<img class="img-fluid w-100"
			src="{{ Storage::exists($imagePath) ? Storage::url($imagePath) : asset('img/landscape-placeholder.svg') }}"
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
			<div class="product-price">
				{{ $product->getPriceForUser() }} Ft
				@if($product->product_unit)
					/ {{ $product->product_unit->measure }}
				@endif
			</div>
		</div>
	</div>
</a>

