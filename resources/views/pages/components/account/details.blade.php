

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
                <section class="order-details-box">    
                    <table class="table tablestripped my-5">
                        <tbody>
                            <tr>
                                <td>Státusz</td>
                                <td>{{ $order->status }}</td>
                            </tr>
                            <tr>
                                <td>Rendelési dátum</td>
                                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y. m. d. H:i') }}</td>
                            </tr>
                            <tr>
                                <td>Fizetési mód</td>
                                <td>{{ $order->payment_method ? 'Készpénz' : 'Átutalás' }}</td>
                            </tr>
                            <tr>
                                <td>Közvetlen szállítás</td>
                                <td>{{ $order->is_direct_shipping ? 'Igen (árazatlan szállító)' : 'Nem' }}</td>
                            </tr>
                            <tr>
                                <td>Szállítási cím</td>
                                <td>
                                    @if($order->user_shipping)
                                        {{ $order->user_shipping->zipcode }}
                                        {{ $order->user_shipping->city }},
                                        {{ $order->user_shipping->address }}
                                    @else
                                        <span class="text-muted">Nem elérhető (korábbi rendelés)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Megjegyzés</td>
                                <td>{!! nl2br(e($order->note)) ?? '-' !!}</td>
                            </tr>
                            <tr>
                                <td>Teljes összeg (nettó)</td>
                                <td>{{ number_format($order->total, 0, ',', ' ') }} Ft</td>
                            </tr>
                            <tr>
                                <td>Áfa (27%)</td>
                                <td>{{ number_format($order->total * 0.27, 0, ',', ' ') }} Ft</td>
                            </tr>
                            <tr>
                                <td>Teljes összeg (bruttó)</td>
                                <td>{{ number_format($order->total * 1.27, 0, ',', ' ') }} Ft</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3 class="order-details-box-title text-center mt-3 pt-2">
                        Rendelt Termékek
                    </h3>
                    <br>
                    <a href="{{route('account.getpdf',$id)}}" class="btn btn-primary">Pdf letöltése</a>

                    <br><br>
                    <table class="table tablestripped">
                        <thead>
                            <th>Termék neve</th>
                            <th>Mennyiség</th>
                            <th>Menny.egység</th>
                            <th>Egység ár</th>
                            <th>Ár</th>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                            <tr>
                                <td>{{$item->product->title}}</td>                                
                                <td>{{$item->quantity}}</td>            
                                <td>{{$item->product->product_unit->measure}}</td>                    
                                <td>
                                    @if(substr($item->product->special_prices->pluck( 'price' ), 2, -2)!="" && substr($item->product->special_prices->pluck( 'user_id' ), 2, -2)==auth()->user()->id)
                                    {{substr($item->product->special_prices->pluck( 'price' ), 2, -2)}}
                                    @else
                                    {{$item->product->price}}
                                    @endif
                                    Ft
                                </td>
                                <td>
                                    @if(substr($item->product->special_prices->pluck( 'price' ), 2, -2)!="" && substr($item->product->special_prices->pluck( 'user_id' ), 2, -2)==auth()->user()->id)
                                    {{(int)substr($item->product->special_prices->pluck( 'price' ), 2, -2)*$item->quantity}}
                                    @else
                                    {{$item->product->price*$item->quantity}}
                                    @endif
                                     Ft
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                
        </section>
            </div>
        </div>
        
        
    </div>
</div>



@endsection
