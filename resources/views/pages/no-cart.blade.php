@extends('layouts.master')
@section('title','Hiányzó adatok!')
@section('content')
<section class="status-v6-page">
    <div class="v6-container v6-container--narrow">
        <span class="v6-eyebrow">A fiók aktiválása szükséges</span>
        <h1>Kosár és rendelés</h1>
        <p class="status-v6-page__lead">A kosár megtekintéséhez és a rendelés leadásához töltsd ki az adataidat, majd hitelesítsd az email-címedet.</p>
        <a href="{{ route('account') }}" class="v6-button v6-button--primary">Fiókom megnyitása</a>
    </div>
</section>
@endsection
