@extends('layouts.admin')
@section('title', 'Termékek')
@section('content')
@php($sortedCategories = collect($categories)->sortBy('name'))
<main class="container">
    <div class="d-flex justify-content-between align-items-center"><h1>Termékek</h1><a href="{{ route('adminpanel.products.create') }}" class="btn btn-primary">Új termék</a></div>
    <form id="product-filter-form" class="card" method="get" action="{{ route('adminpanel.products') }}">
        <div class="card-body row">
            <div class="col-md-3"><label for="product-search">Keresés</label><input id="product-search" type="search" name="search" class="form-control" value="{{ request('search') }}"></div>
            <div class="col-md-3"><label for="product-category">Kategória</label><select id="product-category" name="category_id" class="form-select"><option value="">Összes</option>@foreach($sortedCategories as $category)<option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label for="from-date">Dátumtól</label><input id="from-date" type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
            <div class="col-md-2"><label for="to-date">Dátumig</label><input id="to-date" type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
            <div class="col-md-2"><button class="btn btn-primary" type="submit">Szűrés</button></div>
        </div>
    </form>
    <div id="products-list">@include('admin.pages.products.partials.list', ['products' => $products, 'units' => $units])</div>
</main>
<script>
document.querySelector('#product-filter-form')?.addEventListener('submit', (event) => { event.currentTarget.querySelector('button').disabled = true; });
</script>
@endsection
