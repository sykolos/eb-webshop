@extends('layouts.admin')
@section('title','Dashboard')
@section('content')
<div class="container py-5 m-2">
    <div class="row">
        <div class="col-3">
            <div class="card">
                <div class="card-header bg-light">
                    Elérhető termékek száma:
                </div>
                <div class="card-body">
                    {{$products->count()}}
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-header bg-warning">
                    Nem teljesített rendelések:
                </div>
                <div class="card-body">
                    {{$orders_0->count()}}
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-header bg-success">
                    Teljesített rendelések:
                </div>
                <div class="card-body">
                    {{$orders_1->count()}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection