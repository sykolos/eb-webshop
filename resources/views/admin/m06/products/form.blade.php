@extends('layouts.v6.admin')

@php
    $isEdit = $mode === 'edit';
@endphp
@section('title', $isEdit ? 'Termék szerkesztése' : 'Új termék')
@section('heading', $isEdit ? 'Termék szerkesztése' : 'Új termék')
@section('intro', 'A meglévő árképzés és rendelési adatok változatlanul kapcsolódnak a termékhez.')

@section('content')
@php
    $specificationText = old('specifications_text', collect($product?->specifications ?? [])->map(fn($value, $key) => $key.': '.$value)->implode("\n"));
    $selectedFilterValues = collect(old('filter_value_ids', $product?->filterValues?->pluck('id')->all() ?? []))->map(fn($id) => (int) $id)->all();
@endphp
<div class="v6-section">
    <div class="v6-section__header"><div><span class="v6-eyebrow">Terméktörzs</span><h2>{{ $isEdit ? $product->title : 'Termék alapadatai' }}</h2></div><a class="v6-button v6-button--outline" href="{{ route('adminpanel.products') }}">Vissza a listához</a></div>
    <form method="POST" action="{{ $isEdit ? route('adminpanel.products.update', $product->id) : route('adminpanel.products.store') }}" enctype="multipart/form-data" class="v6-form" x-data="{ tab: 'basic' }">
        @csrf @if($isEdit) @method('PUT') @endif
        <div class="v6-tabs" role="tablist"><button type="button" class="v6-button v6-button--small" :class="{ 'v6-button--outline': tab !== 'basic' }" x-on:click="tab = 'basic'">Alapadatok</button><button type="button" class="v6-button v6-button--small" :class="{ 'v6-button--outline': tab !== 'technical' }" x-on:click="tab = 'technical'">Műszaki adatok</button><button type="button" class="v6-button v6-button--small" :class="{ 'v6-button--outline': tab !== 'media' }" x-on:click="tab = 'media'">Képek és szűrők</button></div>
        <div x-show="tab === 'basic'">
        <div class="v6-form-grid">
            <label>Megnevezés<input name="title" value="{{ old('title', $product->title ?? '') }}" required></label>
            <label>Cikkszám<input name="serialnum" value="{{ old('serialnum', $product->serial_number ?? '') }}" required></label>
            <label>Slug<input name="slug" value="{{ old('slug', $product->slug ?? '') }}" placeholder="Automatikus, ha üres"></label>
            <label>Gyártó<input name="manufacturer" value="{{ old('manufacturer', $product->manufacturer ?? '') }}"></label>
            <label>Alapár<input type="number" min="0" step="1" name="price" value="{{ old('price', $product->price ?? 0) }}" required></label>
            <label>Kategória<select name="category_id" required><option value="">Válassz kategóriát</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ str_repeat('— ', $category->tree_level) }}{{ $category->name }}</option>@endforeach</select></label>
            <label>Kiszerelés<select name="unit_id" required><option value="">Válassz egységet</option>@foreach($product_units as $unit)<option value="{{ $unit->id }}" @selected((string) old('unit_id', $product->unit_id ?? '') === (string) $unit->id)>{{ $unit->unit }} ({{ $unit->quantity }} {{ $unit->measure }})</option>@endforeach</select></label>
            <label>Elérhetőségi státusz<select name="availability_status" required>@foreach(\App\Models\Products::AVAILABILITY_STATUSES as $key => $label)<option value="{{ $key }}" @selected(old('availability_status', $product->availability_status ?? 'in_stock') === $key)>{{ $label }}</option>@endforeach</select></label>
        </div>
        <label class="v6-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $product->is_published ?? true))> Publikus a webshopban</label>
        </div>
        <div x-show="tab === 'technical'">
        <label>Leírás<textarea name="description" rows="6">{{ old('description', $product->description ?? '') }}</textarea></label>
        <label>Műszaki adatok <small>soronként `Kulcs: érték` formában</small><textarea name="specifications_text" rows="6" placeholder="Feszültség: 230 V">{{ $specificationText }}</textarea></label>
        </div>
        <div x-show="tab === 'media'">
        <label>Fő termékkép<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>

        @if($filter_definitions->isNotEmpty())
            <fieldset class="v6-fieldset"><legend>Szűrőértékek</legend>
                @foreach($filter_definitions as $definition)
                    <div class="v6-filter-group"><strong>{{ $definition->name }}</strong><div class="v6-check-grid">@foreach($definition->values as $value)<label class="v6-check"><input type="checkbox" name="filter_value_ids[]" value="{{ $value->id }}" @checked(in_array($value->id, $selectedFilterValues, true))> {{ $value->label }}</label>@endforeach</div></div>
                @endforeach
            </fieldset>
        @endif
        </div>
        @foreach($errors->all() as $error)<p class="v6-form-error">{{ $error }}</p>@endforeach
        <button class="v6-button" type="submit">{{ $isEdit ? 'Mentés' : 'Termék létrehozása' }}</button>
    </form>

    @if($isEdit)
        <section class="v6-subsection"><div class="v6-section__header"><div><h3>Elérhetőségi státusztörténet</h3><p>A státusz nem készletmennyiség; a meglévő rendelhetőségi szabályokat nem írja át.</p></div></div>
            <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Időpont</th><th>Előző</th><th>Új</th><th>Módosító</th><th>Megjegyzés</th></tr></thead><tbody>
            @forelse($product->availabilityHistories as $history)
                <tr><td>{{ $history->created_at?->format('Y-m-d H:i') }}</td><td>{{ \App\Models\Products::AVAILABILITY_STATUSES[$history->from_status] ?? ($history->from_status ?: 'Kezdőállapot') }}</td><td>{{ \App\Models\Products::AVAILABILITY_STATUSES[$history->to_status] ?? $history->to_status }}</td><td>{{ $history->changedBy?->name ?: 'Rendszer' }}</td><td>{{ $history->note ?: '—' }}</td></tr>
            @empty
                <tr><td colspan="5"><div class="v6-empty"><strong>Nincs korábbi státuszváltás</strong></div></td></tr>
            @endforelse
            </tbody></table></div>
        </section>
        <section class="v6-subsection"><div class="v6-section__header"><div><h3>Képek és dokumentumok</h3><p>PDF privát tárhelyre kerül, a képek publikus termékmédiaként mentődnek.</p></div></div>
            <form method="POST" action="{{ route('adminpanel.products.media.store', $product->id) }}" enctype="multipart/form-data" class="v6-actions">@csrf<input type="file" name="media" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf" required><label>Sorrend<input type="number" name="sort_order" min="0" value="0"></label><button class="v6-button v6-button--outline" type="submit">Feltöltés</button></form>
            <div class="v6-media-list">@forelse($product->media as $media)<div class="v6-media-item">@if($media->kind === 'image' && $media->is_public)<img class="v6-media-thumb" src="{{ Storage::disk('public')->url($media->path) }}" alt="{{ $media->original_name }}">@endif<span><strong>{{ $media->kind === 'document' ? 'PDF' : 'Kép' }}</strong> {{ $media->original_name }}<br><small>{{ $media->is_public ? 'Publikus' : 'Privát' }}</small></span><div class="v6-actions">@if($media->is_public && $media->kind === 'image')<a class="v6-button v6-button--small v6-button--outline" target="_blank" href="{{ Storage::disk('public')->url($media->path) }}">Megnyitás</a>@else<a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.products.media.download', [$product->id, $media->id]) }}">Letöltés</a>@endif<form method="POST" action="{{ route('adminpanel.products.media.update', [$product->id, $media->id]) }}" class="v6-actions">@csrf @method('PUT')<label>Sorrend<input type="number" name="sort_order" min="0" value="{{ $media->sort_order }}"></label>@if($media->kind !== 'document')<label class="v6-check"><input type="checkbox" name="is_public" value="1" @checked($media->is_public)> Publikus</label>@endif<button class="v6-button v6-button--small v6-button--outline" type="submit">Mentés</button></form><form method="POST" action="{{ route('adminpanel.products.media.destroy', [$product->id, $media->id]) }}" onsubmit="return confirm('Törlöd ezt a fájlt?')">@csrf @method('DELETE')<button class="v6-button v6-button--small v6-button--danger" type="submit">Törlés</button></form></div></div>@empty<p>Nincs további médiafájl.</p>@endforelse</div>
        </section>
    @endif
</div>
@endsection
