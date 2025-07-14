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
                            <select name="status" class="form-control">
                                <option value="">-- Státusz --</option>
                                @foreach(['függőben', 'feldolgozás', 'kiszállítva', 'törölve'] as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Szűrés</button>
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

    console.log('Spinner elem:', spinner.length);

    form.on('submit', function (e) {
        e.preventDefault();
        spinner.show();
        $.ajax({
            url: '{{ route('adminpanel.orders') }}',
            type: 'GET',
            data: form.serialize(),
            success: function (data) {
                listContainer.html(data);

                // Session frissítéshez – mentjük az aktuális URL-t (szűrőkkel együtt)
                $.post('{{ route('adminpanel.orders.storeFilterUrl') }}', form.serialize());
            },
            error: function () {
                alert('Hiba történt a lekérdezés során.');
            },
            complete: function () {
                spinner.hide();
            }
        });
    });

    // Lapozás AJAX-szal
    $(document).on('click', '#orders-list .pagination a', function (e) {
        e.preventDefault();

        const url = new URL($(this).attr('href'));
        const page = url.searchParams.get("page") || 1;

        spinner.show();

        $.ajax({
            url: url.toString(),
            type: 'GET',
            data: form.serialize(),
            success: function (data) {
                listContainer.html(data);

                // Aktuális szűrők és oldalszám mentése session-be
                const fullQuery = form.serialize() + '&page=' + encodeURIComponent(page);
                $.post('{{ route('adminpanel.orders.storeFilterUrl') }}', fullQuery)
                .fail(function(xhr) {
                    console.error('Mentés nem sikerült:', xhr.responseText);
                });
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
