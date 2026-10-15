@extends('layouts.master')
@section('title','Email ellenőrzése')
@section('content')
<section class="container py-5"><div class="row justify-content-center"><div class="col-lg-7">
    <div class="card p-4">
        <h1 class="h3">Ellenőrizd az email címed</h1>
        @if(session('message') || session('resent'))<div class="alert alert-success" role="status">Az ellenőrző emailt elküldtük.</div>@endif
        <p>A partnerfiók teljes használatához kattints az emailben található ellenőrző linkre. Lejárt link esetén új linket kérhetsz.</p>
        <form method="post" action="{{ route('verification.send') }}">@csrf<button type="submit" class="btn btn-primary">Ellenőrző email újraküldése</button></form>
    </div>
</div></div></section>
@endsection
