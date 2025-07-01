


@extends('layouts.master')
@section('title','Elállási nyilatkozat')
@section('content')
<main class="info-page bg-light">
    
    <section class="">
        <div class="container">
            <div class="row justify-content-center align-items-center page-header-title">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section section-header ">Elállási nyilatkozat</h2>
                </div>
            </div>
            <div class="row justify-content-center align-items-center">
                <div class="col-md-6 text-center mb-5">
                  Az elállás jogáról bővebben az ászf-en belül olvashat.
                  Az elállási nyilatkozat <a href="{{asset('files/elallasi-nyilatkozat-eb.pdf')}}">letöltése itt.</a><br>
                  Kérjük letöltés után töltse ki, majd ezután beolvasva küldje el email címünkre: info@electrobusiness.hu<br>Tárgynak az Elállási nyilatkozatot és a rendelés számát tüntesse fel.
                </div>
            </div>
            
        </div>
    </section>
    
</main>
@endsection