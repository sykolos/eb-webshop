@extends('layouts.public-v6')
@section('title', 'Partner belépés')
@section('content')
@php($returnTo = request('return_to', request('returnTo')))
<section class="auth-v6-page">
    <div class="v6-container auth-v6-layout">
        <aside class="auth-v6-brand">
            <span class="home-v6-eyebrow-light">ELECTRO PARTNER</span>
            <h1>Beszerzés<br><em>egyszerűbben.</em></h1>
            <p>Belépés után eléred a személyre szabott partnerárakat, a mentett rendeléseidet és a gyors újrarendelést.</p>
            <ul>
                <li>Nettó partnerárak és egyedi kedvezmények</li>
                <li>Mentett szállítási adatok</li>
                <li>Átlátható rendeléskövetés</li>
            </ul>
        </aside>
        <div class="auth-v6-panel">
            <nav class="auth-v6-tabs" aria-label="Fiók műveletek">
                <a class="is-active" href="{{ route('login', $returnTo ? ['return_to' => $returnTo] : []) }}">Bejelentkezés</a>
                <a href="{{ route('register', $returnTo ? ['return_to' => $returnTo] : []) }}">Partnerregisztráció</a>
            </nav>
            <div class="auth-v6-heading">
                <span class="v6-eyebrow">PARTNERFÓKÓK</span>
                <h2>Üdv újra itt.</h2>
                <p>Lépj be a rendeléseidhez és a személyre szabott árakhoz.</p>
            </div>
            @if(session('status'))<div class="auth-v6-message auth-v6-message--success" role="status">{{ session('status') }}</div>@endif
            @if(session('success'))<div class="auth-v6-message auth-v6-message--success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())
                <div class="auth-v6-message auth-v6-message--error" role="alert">
                    <strong>Az adatok ellenőrzése szükséges.</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form class="auth-v6-form" action="{{ route('login.submit') }}" method="post">
                @csrf
                @if($returnTo)<input type="hidden" name="return_to" value="{{ $returnTo }}">@endif
                <label for="email">E-mail-cím<input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
                <label for="login-password">Jelszó
                    <span class="auth-v6-password"><input type="password" id="login-password" name="password" autocomplete="current-password" required><button type="button" data-password-toggle data-target="login-password" aria-label="Jelszó megjelenítése">Mutatás</button></span>
                </label>
                <div class="auth-v6-form-row">
                    <label class="auth-v6-check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Maradjak bejelentkezve</label>
                    <a href="{{ route('password.request') }}">Elfelejtetted a jelszavad?</a>
                </div>
                <button class="v6-button v6-button--primary auth-v6-submit" type="submit">Belépés</button>
            </form>
            <p class="auth-v6-footer-link">Még nincs partnerfiókod? <a href="{{ route('register', $returnTo ? ['return_to' => $returnTo] : []) }}">Partnerregisztráció</a></p>
            <p class="auth-v6-footer-link"><a href="{{ route('admin.login') }}">Admin belépés</a></p>
        </div>
    </div>
</section>
@endsection
