@extends('layouts.admin')
@section('title','Rendelés #'.$order->id)
@section('content')
<div class="page-title">Rendelés # {{$order->id}}</div>
<div class="container">
    <div class="row">
        <div class="col-12">
            <a href="{{ session('orders_filter_url', route('adminpanel.orders')) }}" class="btn btn-secondary mb-3">
    ← Vissza a szűrt listához
</a>
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Rendelés részletei</h5>
                </div>

                <div class="card-body">
                    <table class="table tablestripped">
                        <tbody>
                            <tr>
                                <td>Rendelés száma:</td>
                                <td>{{$order->id}}</td>
                            </tr>
                            <tr>
                                <td>Sátusz</td>
                                <td>
                                    <form action="{{route('adminpanel.orders.status.update', $order->id)}}" method="POST" style="display: flex; gap: 15px">
                                    @csrf
                                    <select name="status" id="form-control" style="text-align: center">
                                        @foreach ($states as $status)
                                            <option value="{{$status}}" @if($order->status==$status) selected @endif>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-success">Frissítés</button>
                                    </form>    
                                </td>
                            </tr>
                            <tr>
                                <td>Rendelés összege</td>
                                <td>{{$order->total }} Ft</td>
                            </tr>
                            <tr>
                                <td>Felhasználónév</td>
                                <td>{{$order->user->name}}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>{{$order->email}}</td>
                            </tr>
                            <tr>
                                <td>Telefon</td>
                                <td>{{$order->phone}}</td>
                            </tr>
                            <tr>
                                <td>Ország</td>
                                <td>{{$order->country}}</td>
                            </tr>
                            <tr>
                                <td>Város</td>
                                <td>{{$order->city}}</td>
                            </tr>
                            <tr>
                                <td>Cím</td>
                                <td>{{$order->address}}</td>
                            </tr>
                            <tr>
                                <td>Irányítószám</td>
                                <td>{{$order->zipcode}}</td>
                            </tr>
                            <tr>
                                <td>Dátum</td>
                                <td>{{$order->created_at}}</td>
                            </tr>
                            <tr>
                                <td>Megjegyzés</td>
                                <td>
                                    @if($order->note)
                                        {{ $order->note }}
                                    @else
                                        <em>Nincs megjegyzés</em>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Rendelés részletező</td>
                                <td><a href="{{route('adminpanel.orders.getpdf',$order->id)}}" class="btn btn-primary">Letöltés</a>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
