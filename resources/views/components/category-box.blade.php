@props(['category'])

<div class="category-box text-center h-100 bg-dark">
    <div class="category-image mb-2 bg-light">
        @if($category->image)
            <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="img-fluid" loading="lazy">
        @endif
    </div>
    <div class="category-name fw-bold">
        {{ $category->name }}
    </div>
</div>
