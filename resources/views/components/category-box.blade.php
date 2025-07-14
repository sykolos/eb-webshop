@props(['category'])

<div class="category-box text-center h-100 bg-dark">
    <div class="category-image mb-2">
        {{-- @php
            $imagePath = $product->image;
            @endphp --}}
        <img src="{{asset('img/landscape-placeholder.svg') }}" alt="{{ $category->name }}" loading="lazy" class="img-fluid">
    </div>
    <div class="category-name fw-bold">
        {{ $category->name }}
    </div>
</div>
