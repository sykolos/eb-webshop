@extends('layouts.v6.admin')
@section('title', 'Kategóriák és szűrők')
@section('heading', 'Kategóriák és szűrők')
@section('intro', 'Tetszőleges mélységű kategóriafa, örökölhető webshopos szűrőkkel.')
@section('content')
<div class="v6-section">
    <section class="v6-panel">
        <div class="v6-section__header"><div><span class="v6-eyebrow">Taxonómia</span><h2>Új kategória</h2><p>A ciklusvédelem a mentésnél is érvényesül.</p></div></div>
        <form class="v6-form-grid" method="POST" action="{{ route('adminpanel.category.store') }}">@csrf
            <label>Név<input name="name" value="{{ old('name') }}" required></label>
            <label>Slug<input name="slug" value="{{ old('slug') }}" placeholder="Automatikus, ha üres"></label>
            <label>Szülő kategória<select name="parent_id"><option value="">Gyökér kategória</option>@foreach($category_options as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></label>
            <label>Sorrend<input type="number" min="0" name="sort_order" value="0"></label>
            <label class="v6-check"><input type="checkbox" name="is_active" value="1" checked> Aktív</label>
            <div class="v6-actions"><button class="v6-button" type="submit">Kategória létrehozása</button></div>
        </form>
    </section>

    <section class="v6-panel" style="margin-top:24px">
        <div class="v6-section__header"><div><h2>Kategóriafa</h2><p>{{ count($categories) }} látható csomópont, a termékszám a meglévő törzsadatból jön.</p></div></div>
        <form class="v6-filter-bar" method="GET" action="{{ route('adminpanel.categories') }}"><label>Keresés<input name="search" value="{{ $category_search }}" placeholder="Kategórianév vagy slug"></label><button class="v6-button v6-button--outline" type="submit">Keresés</button>@if($category_search !== '')<a class="v6-button v6-button--outline" href="{{ route('adminpanel.categories') }}">Törlés</a>@endif</form>
        <div class="v6-category-tree"><strong>Hierarchia és breadcrumb</strong><ul class="v6-tree">@include('admin.m06.categories._tree', ['nodes' => $category_roots, 'trail' => []])</ul></div>
        <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Kategória</th><th>Szülő</th><th>Termékek</th><th>Aktív</th><th>Művelet</th></tr></thead><tbody>
        @forelse($categories as $category)
            <tr><td><strong>{{ str_repeat('— ', $category->tree_level) }}{{ $category->name }}</strong><br><small>{{ $category->slug }}</small></td><td>{{ $category->parent?->name ?? 'Gyökér' }}</td><td>{{ $category->products_count ?? $category->products()->count() }}</td><td>{{ $category->is_active ? 'Igen' : 'Nem' }}</td><td><details><summary class="v6-button v6-button--small v6-button--outline">Szerkesztés</summary><form method="POST" action="{{ route('adminpanel.category.update', $category->id) }}" class="v6-form" style="margin-top:10px">@csrf @method('PUT')<label>Név<input name="name" value="{{ $category->name }}" required></label><label>Slug<input name="slug" value="{{ $category->slug }}"></label><label>Szülő<select name="parent_id"><option value="">Gyökér</option>@foreach($category_options as $option)<option value="{{ $option->id }}" @selected($category->parent_id === $option->id)>{{ $option->name }}</option>@endforeach</select></label><label>Sorrend<input type="number" name="sort_order" min="0" value="{{ $category->sort_order }}"></label><label class="v6-check"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Aktív</label><button class="v6-button v6-button--small" type="submit">Mentés</button></form><form method="POST" action="{{ route('adminpanel.category.destroy', $category->id) }}" onsubmit="return confirm('Csak üres, gyermek nélküli kategória törölhető. Folytatod?')">@csrf @method('DELETE')<button class="v6-button v6-button--small v6-button--danger" type="submit">Törlés</button></form></details></td></tr>
        @empty<tr><td colspan="5"><div class="v6-empty"><strong>Nincs kategória</strong><span>Hozd létre az első gyökérkategóriát.</span></div></td></tr>@endforelse
        </tbody></table></div>
    </section>

    <section class="v6-panel" style="margin-top:24px">
        <div class="v6-section__header"><div><span class="v6-eyebrow">M06.04</span><h2>Szűrődefiníciók</h2><p>A kategóriához rendelt szűrők a teljes részfában elérhetők, kedvezményszabályt nem örökölnek.</p></div></div>
        <form method="POST" action="{{ route('adminpanel.filters.store') }}" class="v6-form"><div class="v6-form-grid"><label>Név<input name="name" required></label><label>Típus<select name="type"><option value="select">Egy választás</option><option value="multi">Több választás</option><option value="boolean">Igen/nem</option></select></label><label>Értékek<input name="values[]" placeholder="Első érték"><input name="values[]" placeholder="Második érték"><input name="values[]" placeholder="Harmadik érték"></label><label>Kategóriák<select name="category_ids[]" multiple size="5">@foreach($category_options as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></label></div><button class="v6-button" type="submit">Szűrő létrehozása</button></form>
        <div class="v6-media-list" style="margin-top:18px">@forelse($filter_definitions as $definition)<div class="v6-media-item"><span><strong>{{ $definition->name }}</strong> · {{ $definition->values->pluck('label')->join(', ') ?: 'Nincs érték' }}<br><small>{{ $definition->categories->pluck('name')->join(', ') ?: 'Nincs kategória-hozzárendelés' }} · {{ $definition->is_active ? 'Aktív' : 'Inaktív' }}</small></span><div class="v6-actions"><details><summary class="v6-button v6-button--small v6-button--outline">Szerkesztés</summary><form method="POST" action="{{ route('adminpanel.filters.update', $definition->id) }}" class="v6-form" style="margin-top:10px">@csrf @method('PUT')<label>Név<input name="name" value="{{ $definition->name }}" required></label><label class="v6-check"><input type="checkbox" name="is_active" value="1" @checked($definition->is_active)> Aktív</label><label>Sorrend<input type="number" name="sort_order" min="0" value="{{ $definition->sort_order }}"></label><label>Kategóriák<select name="category_ids[]" multiple size="4">@foreach($category_options as $option)<option value="{{ $option->id }}" @selected($definition->categories->contains('id', $option->id))>{{ $option->name }}</option>@endforeach</select></label><button class="v6-button v6-button--small" type="submit">Mentés</button></form></details><form method="POST" action="{{ route('adminpanel.filters.destroy', $definition->id) }}" onsubmit="return confirm('Törlöd a szűrőt és értékeit?')">@csrf @method('DELETE')<button class="v6-button v6-button--small v6-button--danger" type="submit">Törlés</button></form></div></div>@empty<p>Nincs szűrődefiníció.</p>@endforelse</div>
    </section>
</div>
@endsection
