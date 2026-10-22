@extends('layouts.master')
@section('title', 'Új jelszó beállítása')
@section('content')
<section class="auth-v6-page"><div class="v6-container v6-container--narrow"><div class="auth-v6-panel auth-v6-panel--single">
    <div class="auth-v6-heading"><span class="v6-eyebrow">Fiók hozzáférés</span><h1>Új jelszó beállítása</h1><p>Válassz új jelszót a partnerfiókodhoz.</p></div>
    @if($errors->any())<div class="auth-v6-message" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="auth-v6-form" method="POST" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><label for="email">Email<input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus></label><label for="password">Új jelszó<input id="password" type="password" name="password" required autocomplete="new-password"></label><label for="password-confirm">Új jelszó még egyszer<input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"></label><button type="submit" class="v6-button v6-button--primary auth-v6-submit">Jelszó mentése</button></form>
</div></div></section>
@endsection
