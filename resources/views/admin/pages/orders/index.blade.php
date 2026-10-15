@extends('layouts.admin')
@section('title', 'Rendelések')
@section('content')
<main class="container">
    <h1>Rendelések</h1>
    <form id="order-filter-form" class="card" method="get" action="{{ route('adminpanel.orders') }}">
        <div class="card-body row">
            <div class="col-md-3"><label for="company-name">Cégnév</label><input id="company-name" type="search" name="company_name" class="form-control" value="{{ request('company_name') }}"></div>
            <div class="col-md-3"><label for="order-from">Dátumtól</label><input id="order-from" type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
            <div class="col-md-3"><label for="order-to">Dátumig</label><input id="order-to" type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
            <div class="col-md-2"><label for="order-status">Státusz</label><select id="order-status" name="status" class="form-select"><option value="">Összes</option>@foreach(['függőben', 'feldolgozás', 'kiszállítva', 'törölve'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-md-1"><button class="btn btn-primary" type="submit">Szűrés</button></div>
        </div>
    </form>
    <div id="orders-list">@include('admin.pages.orders.partials.list', ['orders' => $orders])</div>
</main>
<script>
document.querySelector('#order-filter-form')?.addEventListener('submit', (event) => { event.currentTarget.querySelector('button').disabled = true; });
</script>
@endsection
