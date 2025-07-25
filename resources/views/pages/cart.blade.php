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
            <table class="table-responsive">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Termék</th>
                        <th>Egység ár</th>
                        <th>Mennyiség</th>
                        <th>Menny.Egység</th>
                        <th>Összesen</th>
                        <th>Törlés</th>
                    </tr>
                </thead>
                <tbody id="cart-body">
                    
                    @if(session()->has('cart')&&count(session()->get('cart'))>0)
                        
                            @foreach (session()->get('cart') as $key => $item)
                            <tr data-key="{{$key}}" class="cart-row">
                            <td data-label="Termék" class="px-2">
                                <a href="{{route('product',$item['product']['id'])}}" class="cart-item-title d-flex align-items-center gap-2">
                                    <img src="{{ asset('storage/' . $item['product']['image']) }}" alt="" style="width:60px; height:auto">
                                    <p class="m-0">{{$item['product']['title']}}</p>
                                </a>
                            </td>
                            <td data-label="Egység ár">{{ App\Models\Products::find($item['product']['id'])->getPriceForUser() }} Ft</td>
                            <td data-label="Mennyiségi">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary quantity-decrease" data-key="{{ $key }}" data-change="-1">−</button>
                                    <span class="cart-quantity">{{ $item['quantity'] }}</span>
                                    <button class="btn btn-sm btn-outline-secondary quantity-increase" data-key="{{ $key }}" data-change="1">+</button>
                                </div>
                            </td>
                            <td data-label="Menny. egység">{{$item['q'] }}{{ $item['m']}}</td>
                            <td data-label="Összesen" class="cart-subtotal">{{ App\Models\Cart::unitprice($item) * $item['q'] }} Ft</td>
                            <td data-label="Törlés">
                                <button class="btn btn-danger btn-sm cart-remove" data-key="{{ $key }}">x</button>
                            </td>
                        </tr>
                            @endforeach
                        <tr class="cart-total">
                            <td colspan="6" class="text-center">
                                Összesen: <strong>{{ App\Models\Cart::totalamount() }} Ft + Áfa</strong>
                            </td>
                        </tr>
                    @else
                        <tr><td colspan="6" class="empty-cart">A kosarad üres</td></tr>
                    @endif
                </tbody>
            </table>
            <div id="ajax-spinner" style="display:none; position: fixed; top: 30%; left: 50%; z-index: 9999; transform: translate(-50%, -50%);">
                <div class="spinner-border text-danger" role="status" style="width: 3rem; height: 3rem;"></div>
            </div>
        </div>
            <div class="cart-actions">
                <a href="{{route('checkout')}}" class="btn btn-primary btn-lg px-4 me-sm-3 bg-gradient">Tovább a véglegesítéshez</a>
            </div>
    </div>
</main>



@endsection
