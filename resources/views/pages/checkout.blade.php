@extends('layouts.master')
@section('title','Checkout Page')
@section('content')

<div class="row justify-content-center align-items-center page-header-title">
    <div class="col-md-6 text-center mb-5">
        <h2 class="heading-section section-header ">Véglegesités</h2>
    </div>
</div>

<main class="checkout-page">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-12">
                <div class="wrapper bg-dark rounded-3 ">
                    <div class="row no-gutters mb-5">
                        <div class="col-md-5 left-side">
                            <div class="contact-leftside-wrap w-100 p-md-5 p-4">
                                    <h4 class="mb-4 text-light">Megrendelő adatai</h4>
                                    
                                    <div class="row">
                                            <div class="col-md-12">
                                                <ul class="list-group list-group-flush bg-dark">
                                                    <li class="list-group-item bg-dark text-white d-flex">Megrendelő:&nbsp;&nbsp;&nbsp;{{$user_i->company_name}}</li>
                                                    <li class="list-group-item bg-dark text-white">Számlázási cím:&nbsp;&nbsp;&nbsp;{{$user_i->address}}</li>
                                                    <li class="list-group-item bg-dark text-white">Adószám:&nbsp;&nbsp;&nbsp;{{$user_i->vatnumber}}</li>                                                    
                                                    <li class="list-group-item bg-dark text-white">Kiszállítási cím:&nbsp;&nbsp;&nbsp;{{$user_s->address}}</li>
                                                    <li class="list-group-item bg-dark text-white">Átvevő:&nbsp;&nbsp;&nbsp;{{$user_s->receiver}}</li>
                                                    <li class="list-group-item bg-dark text-white">Elérhetőség:&nbsp;&nbsp;&nbsp;{{$user_s->phone}}</li>
                                                  </ul>
                                            </div>
                                        </div>
                            </div>
                        </div>
                        <div class="col-md-7 d-flex bg-light right-side">
                            <div class="cart-mini w-100 p-md-5 p-4">
                            <h3 class="mb-4">Termék összesítő</h3>
                            <table class="table ">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Termék</th>
                                        <th>Egység ár</th>
                                        <th>Mennyiség</th>
                                        <th>M.egység</th>
                                        <th>Összesen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(session()->has('cart')&&count(session()->get('cart'))>0)
                                        
                                            @foreach (session()->get('cart') as $key => $item)
                                            <tr>
                                                <td>
                                                    <p>{{$item['product']['title']}}</p>
                                                </td>
                                                <td>@if(!isset($item['s_price']))
                                                    {{$item['product']['price']}} 
                                                    @else
                                                    {{$item['s_price']}}
                                                    @endif
                                                    Ft</td>                                
                                                <td>{{$item['quantity']*$item['q']}}</td>
                                                <td>{{$item['m']}}</td>
                                                <td>{{App\Models\Cart::unitprice($item)*$item['q']}} Ft</td>
                                            </tr>
                                            @endforeach
                                        <tr class="cart-total">
                                            <td colspan="4" style="text-align:right">Összesen nettó:</td>
                                            <td>{{App\Models\Cart::totalamount()}} Ft</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">Bruttó végösszeg:</td>
                                            <td>{{App\Models\Cart::gettotalvatprice()}} Ft</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">Áfa összesen:</td>
                                            <td>{{App\Models\Cart::gettotalvat()}} Ft</td>
                                        </tr>
                                    @else
                                        <tr><td colspan="6" class="empty-cart">Üres a kosarad</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="row">
            <form action="{{route('validcheckout')}}" id="payment-form" method="post">
                @csrf
            <div class="col-md-12">
                                        <div class="form-group text-center py-3">
                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" required>
                                            <label class="form-check-label" for="flexCheckDefault">
                                                Elolvastam és elfogadom az <a href="{{route('aszf')}}"> ÁSZF-et</a> és  az <a href="{{route('adatkezelesi-nyilatkozat')}}">Adatkezelési nyilatkozatot</a>.
                                            </label>
                                        </div>
                                    </div>
            <div class=" col-12 text-center">
            
                
                    <button type="submit" class="btn btn-primary px-5 py-3">Véglegesités</button>
    
                
            </div>
            </form>
        </div>
    </div>
</main>



@endsection