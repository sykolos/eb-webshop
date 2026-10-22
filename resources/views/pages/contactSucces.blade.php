@extends('layouts.master')
@section('title','Sikeres üzenetküldés!')
@section('content')
<section class="status-v6-page">
    <div class="v6-container v6-container--narrow">
        <span class="v6-eyebrow">Üzenet elküldve</span>
        <h1>Köszönjük a megkeresést</h1>
        <p class="status-v6-page__lead">Hamarosan felvesszük veled a kapcsolatot a megadott email címen keresztül.</p>
        <a href="{{ route('home') }}" class="v6-button v6-button--primary">Vissza a főoldalra</a>
    </div>
</section>
@endsection
