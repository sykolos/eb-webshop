@extends('layouts.master')
@section('title', 'Email ellenőrzése')
@section('content')
<section class="auth-v6-page"><div class="v6-container v6-container--narrow"><div class="auth-v6-panel auth-v6-panel--single">
    <div class="auth-v6-heading"><span class="v6-eyebrow">Partnerfiók</span><h1>Ellenőrizd az email címed</h1><p>A teljes fiókhasználathoz kattints az emailben található ellenőrző linkre.</p></div>
    @if(session('message') || session('resent'))<div class="auth-v6-message auth-v6-message--success" role="status">Az ellenőrző emailt elküldtük.</div>@endif
    <form class="auth-v6-form" method="post" action="{{ route('verification.send') }}">@csrf<button type="submit" class="v6-button v6-button--primary auth-v6-submit">Ellenőrző email újraküldése</button></form>
</div></div></section>
@endsection
