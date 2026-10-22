@extends('layouts.master')
@section('title', 'Jelszó cseréje')
@section('content')
<section class="auth-v6-page"><div class="v6-container v6-container--narrow"><div class="auth-v6-panel auth-v6-panel--single">
    <div class="auth-v6-heading"><span class="v6-eyebrow">Partnerfiók</span><h1>Jelszó cseréje</h1><p>A fiókod biztonsága érdekében használj egyedi, erős jelszót.</p></div>
    @if(session('status'))<div class="auth-v6-message auth-v6-message--success" role="status">{{ session('status') }}</div>@elseif(session('error'))<div class="auth-v6-message" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="auth-v6-message" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="auth-v6-form" action="{{ route('update-password') }}" method="POST">@csrf<label for="oldPasswordInput">Régi jelszó<input name="old_password" type="password" id="oldPasswordInput" required autocomplete="current-password"></label><label for="newPasswordInput">Új jelszó<input name="new_password" type="password" id="newPasswordInput" required autocomplete="new-password"></label><label for="confirmNewPasswordInput">Új jelszó még egyszer<input name="new_password_confirmation" type="password" id="confirmNewPasswordInput" required autocomplete="new-password"></label><button type="submit" class="v6-button v6-button--primary auth-v6-submit">Jelszó mentése</button></form>
</div></div></section>
@endsection
