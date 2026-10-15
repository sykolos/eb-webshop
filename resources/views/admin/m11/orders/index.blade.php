@extends('layouts.v6.admin')

@section('title', 'Rendelések')
@section('heading', 'Rendelések')
@section('intro', 'Keresés, státuszfeldolgozás és kézi rendelésrögzítés egy helyen.')

@section('content')
<div class="v6-section">
    <div class="v6-section__header">
        <div><span class="v6-eyebrow">M11</span><h2>Admin rendeléskezelés</h2><p>{{ $orders->total() }} találat · A kijelölés csak az aktuális találati oldalon értelmezett.</p></div>
        <a class="v6-button v6-button--primary" href="{{ route('adminpanel.orders.create') }}">+ Új rendelés</a>
    </div>

    @if(session('success'))<div class="v6-toast" role="status">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="v6-toast" role="status">{{ session('warning') }} @foreach(session('bulk_errors', []) as $error)<br><strong>{{ $error['order'] }}:</strong> {{ $error['message'] }}@endforeach</div>@endif
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif

    <form id="order-filter-form" class="v6-filter-bar" method="GET" action="{{ route('adminpanel.orders') }}">
        <label>Keresés<input name="search" value="{{ request('search') }}" placeholder="Rendelésszám, partner, e-mail"></label>
        <label>Cégnév<input name="company_name" value="{{ request('company_name') }}" placeholder="Cégnév"></label>
        <label>Státusz<select name="status"><option value="">Minden állapot</option>@foreach($states as $state)<option value="{{ $state }}" @selected(request('status') === $state)>{{ ucfirst($state) }}</option>@endforeach</select></label>
        <label>Minimum nettó<input type="number" min="0" name="min_total" value="{{ request('min_total') }}" placeholder="Ft"></label>
        <label>Maximum nettó<input type="number" min="0" name="max_total" value="{{ request('max_total') }}" placeholder="Ft"></label>
        <label>Ig ettől<input type="date" name="from_date" value="{{ request('from_date') }}"></label>
        <label>Ig eddig<input type="date" name="to_date" value="{{ request('to_date') }}"></label>
        <label class="v6-check"><input type="checkbox" name="include_deleted" value="1" @checked(request('include_deleted'))> Töröltek is</label>
        <div class="v6-actions"><button class="v6-button v6-button--outline" type="submit">Szűrés</button><a class="v6-button v6-button--outline" href="{{ route('adminpanel.orders') }}">Alaphelyzet</a></div>
    </form>

    <form id="bulk-order-form" method="POST" action="{{ route('adminpanel.orders.bulk-status') }}">
        @csrf
        <div class="v6-panel" style="margin-bottom:18px">
            <div class="v6-actions">
                <strong><span id="selected-count">0</span> kijelölve</strong>
                <select name="status" aria-label="Kijelölt rendelések új állapota" required><option value="">Új állapot</option>@foreach($states as $state)<option value="{{ $state }}">{{ ucfirst($state) }}</option>@endforeach</select>
                <input name="note" placeholder="Közös belső megjegyzés (opcionális)" maxlength="2000">
                <button class="v6-button v6-button--primary" type="submit" id="bulk-submit" disabled onclick="return confirm('A kijelölt rendelések állapota módosul. Folytatod?')">Kijelöltek módosítása</button>
                <button class="v6-button v6-button--outline" type="button" id="clear-selection">Kijelölés törlése</button>
            </div>
        </div>
    </form>

    <div id="orders-list">@include('admin.m11.orders.partials.list', ['orders' => $orders, 'states' => $states])</div>
</div>

<script>
(() => {
    const filter = document.getElementById('order-filter-form');
    const boxes = () => [...document.querySelectorAll('[data-order-checkbox]')];
    const count = document.getElementById('selected-count');
    const submit = document.getElementById('bulk-submit');
    const update = () => {
        const selected = boxes().filter((box) => box.checked).length;
        count.textContent = selected;
        submit.disabled = selected === 0;
    };
    document.addEventListener('change', (event) => {
        if (event.target.matches('[data-order-checkbox], [data-select-all]')) {
            if (event.target.matches('[data-select-all]')) boxes().forEach((box) => { box.checked = event.target.checked; });
            update();
        }
    });
    document.getElementById('clear-selection').addEventListener('click', () => { boxes().forEach((box) => { box.checked = false; }); update(); });
    filter.addEventListener('submit', () => boxes().forEach((box) => { box.checked = false; }));
    filter.querySelectorAll('input, select').forEach((field) => field.addEventListener('input', () => {
        boxes().forEach((box) => { box.checked = false; });
        update();
    }));
    filter.querySelectorAll('select, input[type="date"], input[type="number"]').forEach((field) => field.addEventListener('change', () => {
        boxes().forEach((box) => { box.checked = false; });
        update();
    }));
    update();
})();
</script>
@endsection
