@extends('layouts.admin')
@section('title','Rendelések')

{{-- @push('styles')
<style>
    .spinner-container {
        display: none;
        justify-content: center;
        padding: 20px;
    }
</style>
@endpush --}}

@section('content')
<h1 class="page-title">Rendelések</h1>

<div class="container">
    <div class="col-12">

        <!-- Szűrők kártyában -->
        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                <h5>Szűrők</h5>
            </div>
            <div class="card-body">
                <form id="order-filter-form">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="company_name" class="form-control" placeholder="Cégnév">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="from_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="to_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select select2-search">
                                <option value="">-- Státusz --</option>
                                @foreach(['függőben', 'feldolgozás', 'kiszállítva', 'törölve'] as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Spinner -->
        <div class="text-center my-3" id="spinner" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Töltés...</span>
            </div>
        </div>

        <!-- Lista -->
        <div id="orders-list">
            @include('admin.pages.orders.partials.list', ['orders' => $orders])
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    const form = $('#order-filter-form');
    const spinner = $('#spinner');
    const listContainer = $('#orders-list');

    // Initialize Select2 for status filter
    form.find('select[name="status"]').select2({
        allowClear: true,
        theme: 'bootstrap-5',
        language: 'hu',
        width: '100%',
        placeholder: 'Keresés...'
    });

    // Load state from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('company_name')) {
        form.find('input[name="company_name"]').val(urlParams.get('company_name'));
    }
    if (urlParams.has('from_date')) {
        form.find('input[name="from_date"]').val(urlParams.get('from_date'));
    }
    if (urlParams.has('to_date')) {
        form.find('input[name="to_date"]').val(urlParams.get('to_date'));
    }
    if (urlParams.has('status')) {
        form.find('select[name="status"]').val(urlParams.get('status')).trigger('change');
    }

    function updateUrl() {
        const formData = form.serialize();
        const url = new URL(window.location);
        url.search = formData;
        window.history.replaceState({}, '', url);
    }

    function loadOrders(page = 1) {
        let data = form.serialize();
        if (page > 1) {
            data += '&page=' + page;
        }

        updateUrl();

        $.ajax({
            url: '{{ route('adminpanel.orders') }}',
            type: 'GET',
            data: data,
            success: function (data) {
                listContainer.html(data);

                // Session frissítéshez – mentjük az aktuális URL-t (szűrőkkel együtt)
                $.post('{{ route('adminpanel.orders.storeFilterUrl') }}', data);
            },
            error: function () {
                alert('Hiba történt a lekérdezés során.');
            }
        });
    }

    form.on('submit', function (e) {
        e.preventDefault();
        loadOrders(1);
    });

    // Debounce for search input
    let searchTimeout;
    form.find('input[name="company_name"]').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadOrders(1);
        }, 500);
    });

    // Immediate update for select/date filters
    form.find('input[name="from_date"], input[name="to_date"], select[name="status"]').on('change', function() {
        loadOrders(1);
    });

    // Lapozás AJAX-szal
    $(document).on('click', '#orders-list .pagination a', function (e) {
        e.preventDefault();
        const url = new URL($(this).attr('href'));
        const page = url.searchParams.get("page") || 1;
        loadOrders(page);
    });
});
</script>

@endsection
