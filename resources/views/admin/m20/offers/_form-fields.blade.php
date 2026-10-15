@php
    $selectedProducts = old('product_ids', $offer?->products?->pluck('id')->all() ?? []);
    $selectedCategories = old('category_ids', $offer?->categories?->pluck('id')->all() ?? []);
@endphp
<div class="v6-form-grid">
    <label>Cím<input name="title" required maxlength="191" value="{{ old('title', $offer?->title) }}"></label>
    <label>Slug<input name="slug" maxlength="191" value="{{ old('slug', $offer?->slug) }}" placeholder="Automatikus a címből"></label>
    <label>Sorrend<input type="number" name="sort_order" min="0" max="100000" value="{{ old('sort_order', $offer?->sort_order ?? 0) }}"></label>
    <label>Aktív ettől<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $offer?->starts_at?->format('Y-m-d\\TH:i')) }}"></label>
    <label>Aktív eddig<input type="datetime-local" name="ends_at" value="{{ old('ends_at', $offer?->ends_at?->format('Y-m-d\\TH:i')) }}"></label>
    <label class="v6-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $offer?->is_published ?? false))> Mentés publikusként</label>
</div>
<label>Rövid leírás<textarea name="summary" rows="3" maxlength="5000">{{ old('summary', $offer?->summary) }}</textarea></label>
<div class="v6-form-grid">
    <label>Termékek<select name="product_ids[]" multiple size="8">@foreach($products as $product)<option value="{{ $product->id }}" @selected(in_array($product->id, $selectedProducts, true))>{{ $product->title }} · {{ $product->serial_number }}</option>@endforeach</select></label>
    <label>Kategóriák<select name="category_ids[]" multiple size="8">@foreach($categories as $category)<option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategories, true))>{{ str_repeat('— ', $category->tree_level) }}{{ $category->name }}</option>@endforeach</select></label>
</div>
