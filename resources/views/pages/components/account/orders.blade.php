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
                    <p class="orders-box-title section-header">Rendelések</p>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Dátum</th>
                                    <th>Net.Végösszeg</th>
                                    <th>Státusz</th>
                                    <th>Rendelés Azonosító</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(auth()->user()->orders && auth()->user()->orders->count())
                                    @foreach (auth()->user()->orders as $order)
                                        <tr>
                                            <td class="align-middle">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
                                            
                                            <td class="align-middle">{{ $order->total }}</td>
                                            
                                            <td class="align-middle">{{ $order->status }}</td>
                                            <td class="align-middle">EBR-2025-{{ $order->id }}</td>
                                            <td class="align-middle">
                                                <a href="{{ route('account.details', $order->id) }}" class="btn btn-secondary">Részletek</a> 
                                            </td>
                                        </tr>
                                    @endforeach   
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">Nincs megjeleníthető rendelés.</td>
                                    </tr>   
                                @endif
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>
        
    </div>
</div>



@endsection