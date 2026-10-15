@extends('layouts.v6.admin')

@section('title', 'Partnerek és felhasználók')
@section('heading', 'Partnerek és felhasználók')
@section('intro', 'Partneradatok, aktivitás és jogosultsági állapot egy ellenőrzött admin nézetben.')

@section('content')
<div class="v6-section">
    <div class="v6-section__header">
        <div><span class="v6-eyebrow">M12</span><h2>Felhasználói törzs</h2><p>{{ $users->total() }} találat · Az inaktivitás alapja az utolsó rendelés, nem az utolsó belépés.</p></div>
        <a class="v6-button v6-button--outline" href="{{ route('adminpanel.partner-requests') }}">Partnerkérelmek</a>
    </div>

    @if(session('success'))<div class="v6-toast" role="status">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="v6-toast v6-toast--warning" role="status">{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif

    <form method="GET" action="{{ route('adminpanel.users') }}" class="v6-filter-bar">
        <label>Keresés<input name="search" value="{{ request('search') }}" placeholder="ID, név, email, cég, adószám, telefon"></label>
        <label>Fiókállapot<select name="status"><option value="">Minden aktív állapot</option>@foreach(['active','pending','rejected','blocked','archived'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
        <label>Szerepkör<select name="role"><option value="">Minden szerepkör</option><option value="partner" @selected(request('role') === 'partner')>Partner</option><option value="administrator" @selected(request('role') === 'administrator')>Adminisztrátor</option></select></label>
        <label>Inaktív legalább<input type="number" min="1" max="3650" name="inactive_days" value="{{ request('inactive_days') }}" placeholder="nap"></label>
        <label>Létrehozva ettől<input type="date" name="created_from" value="{{ request('created_from') }}"></label>
        <label>Létrehozva eddig<input type="date" name="created_to" value="{{ request('created_to') }}"></label>
        <label>Belépett ettől<input type="date" name="login_from" value="{{ request('login_from') }}"></label>
        <label>Belépett eddig<input type="date" name="login_to" value="{{ request('login_to') }}"></label>
        <label>Utolsó rendelés ettől<input type="date" name="order_from" value="{{ request('order_from') }}"></label>
        <label>Utolsó rendelés eddig<input type="date" name="order_to" value="{{ request('order_to') }}"></label>
        <label class="v6-check"><input type="checkbox" name="without_orders" value="1" @checked(request('without_orders'))> Rendelés nélküliek</label>
        <label class="v6-check"><input type="checkbox" name="include_archived" value="1" @checked(request('include_archived'))> Archiváltak is</label>
        <div class="v6-actions"><button class="v6-button v6-button--primary" type="submit">Szűrés</button><a class="v6-button v6-button--outline" href="{{ route('adminpanel.users') }}">Alaphelyzet</a></div>
    </form>

    <div id="users-list">@include('admin.m12.users.partials.list', ['users' => $users])</div>
</div>
@endsection
