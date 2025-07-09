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
                <form method="GET" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="company_name" class="form-control" placeholder="Cégnév" value="{{ request('company_name') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">-- Státusz --</option>
                                @foreach(['függőben', 'feldolgozás', 'kiszállítva', 'törölve'] as $status)
                                    <option value="{{ $status }}" @if(request('status') == $status) selected @endif>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="number" name="min_total" class="form-control" placeholder="Min Ft" value="{{ request('min_total') }}">
                        </div>
                        <div class="col-md-1">
                            <input type="number" name="max_total" class="form-control" placeholder="Max Ft" value="{{ request('max_total') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Szűrés</button>
                        </div>
                    </div>
                </form>

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
                                
                                <td>
                                    @if($order->user && $order->user->user_invoice)
                                        {{ $order->user->user_invoice->company_name }}
                                    @else
                                        <em>N/A</em>
                                    @endif
                                </td>
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
                    <div class="d-flex justify-content-center mt-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection