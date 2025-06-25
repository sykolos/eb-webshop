@extends('layouts.admin')
@section('title','Rendelések')
@section('content')
<h1 class="page-title">Rendelések</h1>
<div class="container">
        <div class="col-12"> 
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Rendelések</h5>
                </div>
                <div class="card-body">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Megrendelő</th>
                                <th>Tételek</th>
                                <th>Összeg</th>                         
                                <th>Dátum</th>                         
                                <th>Státusz</th>                             
                                <th>Művelet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                            <tr>
                                <td>{{$order->id}}</td>
                                <td>@if($order && $order->name)
                                    <div>{{ $order->name }}</div>
                                @else
                                    <div>Name is missing</div>  <!-- Ha null, itt jelenik meg -->
                                @endif</td>
                                <td>{{$order->items->count()}}</td>
                                <td>{{$order->total}}
                                <td>{{\Carbon\Carbon::parse($order->created_at)->format('d/m/Y')}}</td>
                                <td>
                                    <span class="badge bg-@if($order->status=='függőben')warning
                                        @elseif($order->status=='feldolgozás')info
                                        @elseif($order->status=='kiszállítva')success
                                        @elseif($order->status=='törölve')warning @endif
                                    ">
                                    {{$order->status}}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex" style="gap: 5px">

                                    <a href="{{route('adminpanel.orders.view',$order->id)}}" class="btn btn-secondary">Megtekint</a> 
                                    
                                     
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection