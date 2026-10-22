@extends('layouts.public-v6')
@section('title', 'Partnerfiók igénylése')
@section('content')
@php($returnTo = request('return_to', request('returnTo')))
<section class="auth-v6-page">
    <div class="v6-container auth-v6-layout">
        <aside class="auth-v6-brand">
            <span class="home-v6-eyebrow-light">ELECTRO PARTNER</span>
            <h1>Építsünk<br><em>jobb beszerzést.</em></h1>
            <p>Partnerfiókoddal a vállalkozásodhoz igazított árakat és kényelmesebb rendelési folyamatot kapsz.</p>
            <ul>
                <li>Személyre szabott árképzés</li>
                <li>Gyorsabb újrarendelés</li>
                <li>Partneri ügyintézés egy helyen</li>
            </ul>
        </aside>
        <div class="auth-v6-panel">
            <nav class="auth-v6-tabs" aria-label="Fiók műveletek">
                <a href="{{ route('login', $returnTo ? ['return_to' => $returnTo] : []) }}">Bejelentkezés</a>
                <a class="is-active" href="{{ route('register', $returnTo ? ['return_to' => $returnTo] : []) }}">Partnerregisztráció</a>
            </nav>
            <div class="auth-v6-heading">
                <span class="v6-eyebrow">PARTNERKÉRELEM</span>
                <h2>Indítsuk el az együttműködést.</h2>
                <p>A kérelmet jóváhagyás után aktiváljuk. A megadott e-mail-címen értesítünk.</p>
            </div>
            @if($errors->any())
                <div class="auth-v6-message auth-v6-message--error" role="alert">
                    <strong>Az adatok ellenőrzése szükséges.</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form class="auth-v6-form auth-v6-form--registration" action="{{ route('register.submit') }}" method="post">
                @csrf
                <div class="auth-v6-form-grid">
                    <label for="name">Kapcsolattartó neve<input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required></label>
                    <label for="email">E-mail-cím<input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required></label>
                    <label for="company_name">Cégnév<input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" autocomplete="organization" required></label>
                    <label for="vatnumber">Adószám<input type="text" id="vatnumber" name="vatnumber" value="{{ old('vatnumber') }}"></label>
                    <label for="phone">Telefonszám<input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel"></label>
                    <label for="password">Jelszó
                        <span class="auth-v6-password"><input type="password" id="password" name="password" autocomplete="new-password" required><button type="button" data-password-toggle data-target="password" aria-label="Jelszó megjelenítése">Mutatás</button></span>
                    </label>
                    <label for="password-confirm">Jelszó újra
                        <span class="auth-v6-password"><input type="password" id="password-confirm" name="password_confirmation" autocomplete="new-password" required><button type="button" data-password-toggle data-target="password-confirm" aria-label="Jelszó megjelenítése">Mutatás</button></span>
                    </label>
                </div>
                <label class="auth-v6-field--full" for="message">Megjegyzés<textarea id="message" name="message" rows="3">{{ old('message') }}</textarea></label>
                <label class="auth-v6-check auth-v6-check--top"><input type="checkbox" required> Elolvastam az <a href="{{ route('adatkezelesi-nyilatkozat') }}">adatkezelési tájékoztatót</a>.</label>
                <button class="v6-button v6-button--primary auth-v6-submit" type="submit">Partnerkérelem beküldése</button>
            </form>
            <p class="auth-v6-footer-link">Már van partnerfiókod? <a href="{{ route('login', $returnTo ? ['return_to' => $returnTo] : []) }}">Belépés</a></p>
        </div>
    </div>
</section>
@endsection
