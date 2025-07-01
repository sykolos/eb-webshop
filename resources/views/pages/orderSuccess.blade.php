@extends('layouts.master')
@section('title','Sikeres Rendelés!')
@section('content')

<div class="row justify-content-center align-items-center page-header-title">
    <div class="col-md-6 text-center mb-5">
        <h2 class="heading-section section-header ">Sikeres Rendelés!</h2>
    </div>
</div>
<section class="page-success">
    <div class="container">
        <h1>Sikeres Rendelés</h1>
        <h2>Rendelés azonositó: {{$order->id}}</h2>
    </div>
</section>
@endsection