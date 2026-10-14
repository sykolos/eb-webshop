

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
                                <td>Rendelésazonosító</td>
                                <td>EBR-2025-{{ $order->id }}</td>
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
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table w-100">
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
                                    {{$item->unit_price}}
                                    Ft
                                </td>
                                <td>
                                    {{$item->unit_price * $item->quantity}}
                                     Ft
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                        </div>
                    </div>
                    <div class="d-md-none">
                        <div class="row">
                            @foreach ($order->items as $item)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="product-card border rounded p-3 bg-white shadow-sm h-100">
                                        <p><strong>🛒 Termék:</strong> {{ $item->product->title }}</p>
                                        <p><strong>Mennyiség:</strong> {{ $item->quantity }} {{ $item->product->product_unit->measure }}</p>
                                        <p><strong>Egységár:</strong>
                                            {{ number_format($item->unit_price, 0, ',', ' ') }} Ft
                                        </p>
                                        <p><strong>Összesen:</strong>
                                            {{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} Ft
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                

        </section>
            </div>
        </div>
        
        
    </div>
</div>



@endsection
