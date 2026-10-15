@extends('layouts.v6.admin')

@section('title', 'Partner #'.$user->id)
@section('heading', $user->user_invoice?->company_name ?: $user->name)
@section('intro', 'Partner #'.$user->id.' · létrehozva '.optional($user->created_at)->format('Y. m. d. H:i'))

@section('content')
<div class="v6-section">
    <div class="v6-section__header">
        <div><span class="v6-eyebrow">M12 adatlap</span><h2>{{ $user->name }}</h2><p>{{ $user->email }} · utolsó belépés: {{ optional($user->last_login_at)->format('Y. m. d. H:i') ?: 'ismeretlen' }} · utolsó látogatás: {{ optional($user->last_seen_at)->format('Y. m. d. H:i') ?: 'ismeretlen' }} · utolsó rendelés: {{ optional($user->orders->first()?->created_at)->format('Y. m. d. H:i') ?: 'nincs' }}</p></div>
        <a class="v6-button v6-button--outline" href="{{ session('users_filter_url', route('adminpanel.users')) }}">Vissza a listához</a>
    </div>
    @if(session('success'))<div class="v6-toast" role="status">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="v6-toast v6-toast--warning" role="status">{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('adminpanel.users.user_update', $user->id) }}" class="v6-form">
        @csrf @method('PUT')
        <div class="v6-form-grid">
            <label>Név<input name="name" value="{{ old('name', $user->name) }}" required></label>
            <label>Email<input type="email" name="email" value="{{ old('email', $user->email) }}" required></label>
            <label>Fiókállapot<select name="account_status" required>@foreach(['active','pending','rejected','blocked'] as $status)<option value="{{ $status }}" @selected(old('account_status', $user->account_status ?: 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
            <label>Szerepkör<select name="role" required><option value="partner" @selected(old('role', $user->technicalRole()) === 'partner')>Partner</option><option value="administrator" @selected(old('role', $user->technicalRole()) === 'administrator')>Adminisztrátor</option></select></label>
        </div>
        <label>Admin megjegyzés<textarea name="admin_note" rows="4" maxlength="5000">{{ old('admin_note', $user->admin_note) }}</textarea></label>

        <fieldset class="v6-fieldset"><legend>Számlázási adatok</legend><div class="v6-form-grid">
            @foreach(['company_name'=>'Cégnév','address'=>'Cím','city'=>'Város','state'=>'Megye / állam','zipcode'=>'Irányítószám','country'=>'Ország','vatnumber'=>'Adószám','registrynumber'=>'Cégjegyzékszám'] as $field => $label)
                <label>{{ $label }}<input name="invoice[{{ $field }}]" value="{{ old('invoice.'.$field, $user->user_invoice?->{$field}) }}" @required($field === 'company_name')></label>
            @endforeach
        </div></fieldset>

        <fieldset class="v6-fieldset"><legend>Szállítási címek</legend>
            @forelse($user->user_shipping as $address)
                <div class="v6-subsection" style="margin-top:0">
                    <strong>{{ $address->is_default ? 'Alapértelmezett cím' : 'Mentett cím #'.$address->id }}</strong>
                    <div class="v6-form-grid">
                        @foreach(['receiver'=>'Átvevő','phone'=>'Telefon','zipcode'=>'Irányítószám','city'=>'Város','address'=>'Cím','comment'=>'Futármegjegyzés'] as $field => $label)
                            <label>{{ $label }}<input name="shipping[{{ $address->id }}][{{ $field }}]" value="{{ old('shipping.'.$address->id.'.'.$field, $address->{$field}) }}" @required($field === 'address')></label>
                        @endforeach
                    </div>
                    <label class="v6-check"><input type="hidden" name="shipping[{{ $address->id }}][is_default]" value="0"><input type="checkbox" name="shipping[{{ $address->id }}][is_default]" value="1" @checked(old('shipping.'.$address->id.'.is_default', $address->is_default))> Alapértelmezett cím</label>
                </div>
            @empty
                <p>Nincs mentett szállítási cím.</p>
            @endforelse
            <div class="v6-subsection" style="margin-top:0"><strong>Új cím hozzáadása</strong><div class="v6-form-grid">
                @foreach(['receiver'=>'Átvevő','phone'=>'Telefon','zipcode'=>'Irányítószám','city'=>'Város','address'=>'Cím','comment'=>'Futármegjegyzés'] as $field => $label)
                    <label>{{ $label }}<input name="new_shipping[{{ $field }}]" value="{{ old('new_shipping.'.$field) }}"></label>
                @endforeach
            </div></div>
        </fieldset>

        <div class="v6-actions"><button class="v6-button v6-button--primary" type="submit">Adatok mentése</button><span class="v6-status v6-status--neutral">{{ $user->special_prices_count }} külön ár</span><span class="v6-status v6-status--neutral">{{ $user->orders->count() }} legutóbbi rendelés betöltve</span></div>
    </form>

    <section class="v6-panel" style="margin-top:18px"><div class="v6-section__header"><div><h2>Admin műveletek</h2><p>A jelszó nem látható és nem szerkeszthető; csak egyszer használható visszaállítási link kérhető.</p></div></div><div class="v6-actions">
        @if(!$user->trashed())
            <form method="POST" action="{{ route('adminpanel.users.user_password_reset', $user->id) }}">@csrf<button class="v6-button v6-button--outline" type="submit">Jelszó-visszaállító link</button></form>
            @if(!$user->is_admin)<form method="POST" action="{{ route('adminpanel.users.user_destroy', $user->id) }}" onsubmit="return confirm('A partner aktív listából archiválásra kerül, a rendelési adatok megmaradnak. Folytatod?')">@csrf @method('DELETE')<button class="v6-button v6-button--danger" type="submit">Partner archiválása</button></form>@endif
        @else
            <form method="POST" action="{{ route('adminpanel.users.user_restore', $user->id) }}">@csrf<button class="v6-button v6-button--primary" type="submit">Partner visszaállítása</button></form>
        @endif
    </div></section>

    <section class="v6-panel" style="margin-top:18px"><div class="v6-section__header"><div><h2>Kapcsolódó rendelések</h2><p>Az itt látható rendelések történeti partnerkapcsolata megmarad archiválás után is.</p></div></div><div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Rendelés</th><th>Státusz</th><th>Összeg</th><th>Dátum</th><th></th></tr></thead><tbody>@forelse($user->orders as $order)<tr><td>{{ $order->displayNumber() }}</td><td>{{ $order->status }}</td><td>{{ number_format((float) ($order->subtotal_net ?: $order->total), 0, ',', ' ') }} Ft nettó</td><td>{{ optional($order->created_at)->format('Y. m. d. H:i') }}</td><td><a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.orders.view', $order->id) }}">Adatlap</a></td></tr>@empty<tr><td colspan="5">Nincs rendelés.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endsection
