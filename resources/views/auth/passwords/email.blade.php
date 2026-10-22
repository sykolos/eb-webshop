@extends('layouts.master')
@section('title', 'Jelszó visszaállítása')
@section('content')
<section class="auth-v6-page"><div class="v6-container v6-container--narrow"><div class="auth-v6-panel auth-v6-panel--single">
    <div class="auth-v6-heading"><span class="v6-eyebrow">Fiók hozzáférés</span><h1>Jelszó visszaállítása</h1><p>Írd be a partnerfiókhoz tartozó email címet.</p></div>
    @if(session('status'))<div class="auth-v6-message auth-v6-message--success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="auth-v6-message" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="auth-v6-form" method="POST" action="{{ route('password.email') }}">@csrf<label for="email">Email<input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus></label><button type="submit" class="v6-button v6-button--primary auth-v6-submit">Visszaállító link küldése</button></form>
</div></div></section>
@endsection
