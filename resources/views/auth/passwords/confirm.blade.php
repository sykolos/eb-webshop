@extends('layouts.master')
@section('title', 'Jelszó megerősítése')
@section('content')
<section class="auth-v6-page"><div class="v6-container v6-container--narrow"><div class="auth-v6-panel auth-v6-panel--single">
    <div class="auth-v6-heading"><span class="v6-eyebrow">Védett művelet</span><h1>Erősítsd meg a jelszavad</h1><p>A folytatáshoz írd be újra a partnerfiókod jelszavát.</p></div>
    @if($errors->any())<div class="auth-v6-message" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="auth-v6-form" method="POST" action="{{ route('password.confirm') }}">@csrf<label for="password">Jelszó<input id="password" type="password" name="password" required autocomplete="current-password"></label><div class="auth-v6-form-row"><button type="submit" class="v6-button v6-button--primary">Megerősítés</button>@if(Route::has('password.request'))<a href="{{ route('password.request') }}">Elfelejtetted a jelszavad?</a>@endif</div></form>
</div></div></section>
@endsection
