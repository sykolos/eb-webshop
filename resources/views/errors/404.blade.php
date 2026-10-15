@extends('layouts.v6.shop')
@section('title','Az oldal nem található')
@section('content')
<section class="v6-container v6-container--narrow v6-m14-page">
    <div class="v6-empty-state"><span class="v6-eyebrow">404</span><strong>Ez az oldal nem található.</strong><span>A hivatkozás lejárhatott vagy megváltozott. Innen folytathatod a böngészést.</span><a class="v6-button v6-button--primary" href="{{ route('home') }}">Vissza a főoldalra</a></div>
</section>
@endsection
