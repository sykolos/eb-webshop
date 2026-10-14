@extends('layouts.v6.admin')

@section('title', 'Terméktörzs')
@section('heading', 'Terméktörzs')
@section('intro', 'Publikálás, elérhetőség, kategória és csomagolás egy helyen.')

@section('content')
<div class="v6-section">
    <div class="v6-section__header">
        <div>
            <span class="v6-eyebrow">M06</span>
            <h2>Termékek</h2>
            <p>{{ $products->total() }} találat, a történeti rendelések archiválással védve.</p>
        </div>
        <div class="v6-actions">
            <a class="v6-button v6-button--outline" href="{{ route('adminpanel.products.data-quality') }}">Adatminőség</a>
            <a class="v6-button" href="{{ route('adminpanel.products.create') }}">Új termék</a>
        </div>
    </div>

    <form class="v6-filter-bar" method="GET" action="{{ route('adminpanel.products') }}">
        <label>Keresés<input name="search" value="{{ request('search') }}" placeholder="Név, cikkszám vagy gyártó"></label>
        <label>Kategória<select name="category_id">
            <option value="">Minden kategória</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ str_repeat('— ', $category->tree_level) }}{{ $category->name }}</option>
            @endforeach
        </select></label>
        <label>Elérhetőség<select name="availability_status">
            <option value="">Minden állapot</option>
            @foreach(\App\Models\Products::AVAILABILITY_STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('availability_status') === $key)>{{ $label }}</option>
            @endforeach
        </select></label>
        <label>Publikálás<select name="published">
            <option value="">Mindegy</option>
            <option value="1" @selected(request('published') === '1')>Publikus</option>
            <option value="0" @selected(request('published') === '0')>Rejtett</option>
        </select></label>
        <label class="v6-check"><input type="checkbox" name="include_archived" value="1" @checked(request('include_archived'))> Archiváltak is</label>
        <button class="v6-button v6-button--outline" type="submit">Szűrés</button>
    </form>

    @include('admin.m06.products.partials.list', ['products' => $products])
</div>
@endsection
