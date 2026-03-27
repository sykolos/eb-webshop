@extends('layouts.admin')
@section('title','Termékek')
@section('content')
<?php
// Sort categories alphabetically
$sortedCategories = collect($categories)->sortBy('name');
?>
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
                        <select name="category_id" class="form-select select2-search">
                            <option value="">-- Kategória --</option>
                            @foreach ($sortedCategories as $category)
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

    // Initialize Select2 for category filter
    form.find('select[name="category_id"]').select2({
        allowClear: true,
        theme: 'bootstrap-5',
        language: 'hu',
        width: '100%',
        placeholder: 'Keresés...'
    });

    // Load state from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) {
        form.find('input[name="search"]').val(urlParams.get('search'));
    }
    if (urlParams.has('category_id')) {
        form.find('select[name="category_id"]').val(urlParams.get('category_id')).trigger('change');
    }
    if (urlParams.has('from_date')) {
        form.find('input[name="from_date"]').val(urlParams.get('from_date'));
    }
    if (urlParams.has('to_date')) {
        form.find('input[name="to_date"]').val(urlParams.get('to_date'));
    }

    function updateUrl() {
        const formData = form.serialize();
        const url = new URL(window.location);
        url.search = formData;
        window.history.replaceState({}, '', url);
    }

    function loadProducts(page = 1) {
        let data = form.serialize();
        if (page > 1) {
            data += '&page=' + page;
        }

        updateUrl();

        $.ajax({
            url: '{{ route('adminpanel.products') }}',
            type: 'GET',
            data: data,
            success: function (data) {
                listContainer.html(data);
                
                // Reattach pagination handlers
                $(document).on('click', '#products-list .pagination a', function (e) {
                    e.preventDefault();
                    const pageUrl = new URL($(this).attr('href'));
                    const page = pageUrl.searchParams.get('page') || 1;
                    loadProducts(page);
                });
            },
            error: function () {
                alert('Hiba történt a szűrés során.');
            }
        });
    }

    form.on('submit', function (e) {
        e.preventDefault();
        loadProducts(1);
    });

    // Debounce for search input
    let searchTimeout;
    form.find('input[name="search"]').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadProducts(1);
        }, 500);
    });

    // Immediate update for select filters
    form.find('select[name="category_id"], input[name="from_date"], input[name="to_date"]').on('change', function() {
        loadProducts(1);
    });

    $(document).on('click', '#products-list .pagination a', function (e) {
        e.preventDefault();
        const pageUrl = new URL($(this).attr('href'));
        const page = pageUrl.searchParams.get('page') || 1;
        loadProducts(page);
    });
});
</script>
@endsection

