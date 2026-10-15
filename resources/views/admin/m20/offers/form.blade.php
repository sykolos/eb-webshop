@extends('layouts.v6.admin')

@section('title', 'Ajánlat szerkesztése')
@section('heading', 'Ajánlat szerkesztése')
@section('intro', 'Az ajánlati kiemelés tartalma módosítható, a kapcsolódó partneri ármegállapodások érintetlenek maradnak.')

@section('content')
<div class="v6-section">
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('adminpanel.offers.update', $offer) }}" class="v6-form">
        @csrf @method('PUT')
        @include('admin.m20.offers._form-fields', ['offer' => $offer])
        <div class="v6-actions"><button class="v6-button v6-button--primary" type="submit">Módosítások mentése</button><a class="v6-button v6-button--outline" href="{{ route('adminpanel.offers') }}">Vissza a listához</a></div>
    </form>
</div>
@endsection
