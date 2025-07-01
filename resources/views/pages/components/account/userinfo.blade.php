@extends('pages.account')
@section('title','Rendelések')
@section('content')
<div class="accounts-page bg-light"> 
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-12">
                
                @include('pages.components.account.header')

            </div>
            <div class="col-lg-9 col-md-8 col-sm-12">
                
        <section class="user-info-box">
            <p class="user-info-box-title section-header">
                Felhasználó adatok
            </p>
            <div class="row">
                <div class="col-6 invoce-data">
                        <p>Számlázási adatok:</p>
                        <p>Cégnév: {{auth()->user()->user_invoice->company_name ?? " "}}</p>
                        <p>Ország: {{auth()->user()->user_invoice->country ?? " "}}</p>
                        <p>Megye: {{auth()->user()->user_invoice->state ?? " "}}</p>
                        <p>Számlázási cím: {{auth()->user()->user_invoice->zipcode ?? " "}}, {{auth()->user()->user_invoice->city ?? " "}}&nbsp;{{auth()->user()->user_invoice->address ?? " "}}</p>
                        
                        <p>Adószám: {{auth()->user()->user_invoice->vatnumber ?? " "}}</p>
                        <!--<p>Cégjegyzékszám: {{auth()->user()->user_invoice->registrynumber ?? " "}}</p>-->
                        
                </div>
                <div class="col-6 shipping-data">
                        <p>Kiszállízási adatok:</p>
                        <p>Átvevő: {{auth()->user()->user_shipping->receiver ?? " "}}</p>
                        <p>Átvevő telefonszáma:{{auth()->user()->user_shipping->phone ?? " "}}</p>
                        <p>Kiszállítási cím: {{auth()->user()->user_shipping->zipcode ?? " "}}, {{auth()->user()->user_shipping->city ?? " "}}&nbsp;{{auth()->user()->user_shipping->address ?? " "}}</p>
                        <p>Megjegyzés a futárnak: {{auth()->user()->user_shipping->comment ?? " "}}</p>
                    
                </div>
            </div>
            <a class="btn btn-primary my-2 bg-gradient" href="{{route('account.modify')}}">Adatok Szerekesztése</a>
            
            
        </section>

            </div>
        </div>
    </div>
</div>



@endsection