@extends('layouts.admin')
@section('title','Felhasználó #'.$user->id)
@section('content')
<div class="page-title">Felhasználó # {{$user->id}}</div>
<div class="container user-view">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Felhasználó adatai</h5>
                </div>
                <div class="card-body">
                    <table class="table tablestripped">
                        <tbody>
                            <tr>
                                <td>Felhasználó id</td>
                                <td>{{$user->id}}</td>
                            </tr>
                            <tr>
                                <td>Felhasználó név</td>
                                <td>{{$user->name}}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>{{$user->email}}</td>
                            </tr>
                            <tr>
                                <td>Létrehozva</td>
                                <td>{{$user->created_at}}</td>
                            </tr>                       
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    </div>
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Számlázási adatok</h5>
                </div>
                <div class="card-body">
                    <table class="table tablestripped">
                        <tbody>
                            <tr>
                                <td>Cím</td>
                                <td>{{$user->user_invoice->zipcode.", ".$user->user_invoice->city." ".$user->user_invoice->address}}</td>
                            </tr>
                            <tr>
                                <td>Adószám</td>
                                <td>{{$user->user_invoice->vatnumber}}</td>
                            </tr> 
                            <tr>
                                <td>Cég név</td>
                                <td>{{$user->user_invoice->company_name}}</td>
                            </tr>                          
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    </div>
        <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>Kiszállítási adatok</h5>
            </div>
            <div class="card-body">
                @if ($user->user_shippings && $user->user_shippings->count())
                    @foreach ($user->user_shippings as $shipping)
                        <div class="mb-4 border-bottom pb-3">
                            <p class="mb-1"><strong>Átvevő:</strong> {{ $shipping->receiver }}</p>
                            <p class="mb-1"><strong>Telefonszám:</strong> {{ $shipping->phone }}</p>
                            <p class="mb-1"><strong>Cím:</strong> {{ $shipping->zipcode }}, {{ $shipping->city }} {{ $shipping->address }}</p>
                            @if($shipping->comment)
                                <p class="mb-1"><strong>Megjegyzés a futárnak:</strong> {{ $shipping->comment }}</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <em>Nincs kiszállítási cím rögzítve</em>
                @endif
            </div>
        </div>
    </div>
</div>

        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    Admin 
                </div>
                <div class="card-body">
                    <table class="table tablestripped">
                        <tbody>
                            <tr>
                                <td>Admin</td>
                                @if($user->is_admin==1)
                                <td>Admin</td>
                                <td><a href="#" class="btn btn-danger">Megváltoztat</a></td>
                                @else
                                <td>Nem admin</td>
                                <td><a href="#" class="btn btn-danger">Megváltoztat</a></td>
                                @endif
                            </tr>                           
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
