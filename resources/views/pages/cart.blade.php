@extends('layouts.master')
@section('title','Cart Page')
@section('content')


<div class="row justify-content-center align-items-center page-header-title">
    <div class="col-md-6 text-center mb-5">
        <h2 class="heading-section section-header ">Kosár</h2>
    </div>
</div>

@if (session()->has('success'))
<section class="pop-up">
    <div class="pop-up-box">
        <div class="pop-up-title">
            {{session()->get('success')}}
        </div>
        <div class="pop-up-actions">
            <a href="{{route('home')}}" class="btn btn-outlined">Continue Shopping</a>
        </div>
    </div>
</section>
@endif

<main class="cart-page">
    <div class="container">
        <div class="cart-table">
            <table class="table">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Termék neve</th>
                        <th>Egység ár</th>
                        <th>Mennyiség</th>
                        <th>Menny.Egység</th>
                        <th>Összesen</th>
                        <th>Törlés</th>
                    </tr>
                </thead>
                <tbody>
                    @if(session()->has('cart')&&count(session()->get('cart'))>0)
                        
                            @foreach (session()->get('cart') as $key => $item)
                            <tr>
                                <td class="px-2">
                                    <a href="{{route('product',$item['product']['id'])}}" class="cart-item-title">
                                    <img src="{{asset('storage/public/'.$item['product']['image'])}}" alt="">
                                    <p>{{$item['product']['title']}}</p>
                                    </a>                                    
                                </td>
                                <td>@if(!isset($item['s_price']))
                                    {{$item['product']['price']}} 
                                    @else
                                    {{$item['s_price']}}
                                    @endif
                                </td>                                
                                <td>{{$item['quantity']*$item['q']}}</td>                                
                                <td>{{$item['m']}}</td>
                                <td>{{App\Models\Cart::unitprice($item)*$item['q']}} Ft</td>
                                <td>
                                    <form action="{{route('removefromcart',$key)}}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">X</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        <tr class="cart-total">
                            <td colspan="4" style="text-align:right">Összesen:</td>
                            <td>{{App\Models\Cart::totalamount()}}Ft +Áfa</td>
                            <td></td>
                        </tr>
                    @else
                        <tr><td colspan="6" class="empty-cart">A kosarad üres</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
            <div class="cart-actions">
                <a href="{{route('checkout')}}" class="btn btn-primary">Tovább a véglegesítéshez</a>
            </div>
    </div>
</main>



@endsection