@extends('layouts.admin')
@section('title','Termékek')
@section('content')
<h1 class="page-title">Termékek</h1>

<div class="container">
    <div class="text-end mb-3">
        <a href="{{ route('adminpanel.products.create') }}" class="btn btn-primary">Create Product</a>
    </div>

    {{-- Szűrő card --}}
    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            <h5>Szűrés</h5>
        </div>
        
        <div class="card-body">
            <form id="product-filter-form">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Keresés név alapján">
                    </div>
                    
                    <div class="col-md-2">
                        <select name="category_id" class="form-select">
                            <option value="">-- Kategória --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Szűrés</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Spinner --}}
    <div class="text-center my-3" id="spinner" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Töltés...</span>
        </div>
    </div>

    {{-- Terméklista AJAX-szal frissül --}}
    <div id="products-list">
        @include('admin.pages.products.partials.list', ['products' => $products, 'units' => $units])
    </div>
</div>
<script>
$(document).ready(function () {
    const form = $('#product-filter-form');
    const spinner = $('#spinner');
    const listContainer = $('#products-list');

    spinner.hide();

    form.on('submit', function (e) {
        e.preventDefault();
        spinner.show();
        $.ajax({
            url: '{{ route('adminpanel.products') }}',
            type: 'GET',
            data: form.serialize(),
            success: function (data) {
                listContainer.html(data);
            },
            complete: function () {
                spinner.hide();
            },
            error: function () {
                alert('Hiba történt a szűrés során.');
            }
        });
    });

    $(document).on('click', '#products-list .pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        spinner.show();
        $.ajax({
            url: url,
            type: 'GET',
            data: form.serialize(),
            success: function (data) {
                listContainer.html(data);
            },
            complete: function () {
                spinner.hide();
            },
            error: function () {
                alert('Hiba történt a lapozás során.');
            }
        });
    });
});
</script>
@endsection

