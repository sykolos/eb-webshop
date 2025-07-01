

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
                <section class="orders-box">
                    <p class="orders-box-title section-header">
                        Rendelések
                    </p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Termékek</th>
                                <th>Net.Végösszeg</th>
                                <th>Dátum</th>
                                <th>Státusz</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(auth()->user()->orders && auth()->user()->orders->count())
                            @foreach (auth()->user()->orders as $order)
                            <tr>
                                <td>{{$order->id}}</td>
                                <td>{{$order->items->count()}}</td>
                                <td>{{$order->total}}</td>
                                <td>{{\Carbon\Carbon::parse($order->created_at)->format('d/m/Y')}}</td>
                                <td>{{$order->status}}</td>
                                <td>
                                <a href="{{route('account.details',$order->id)}}" class="btn btn-secondary">Részletek</a> 
                                </td>
                            </tr>
                            @endforeach   
                            @else
                            <tr>
                                <td colspan="5" style="text-align: center">Nincs megjelenithető rendelés.</td>
                            </tr>   
                            @endif
                            
                
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
        
    </div>
</div>



@endsection