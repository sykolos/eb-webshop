@extends('layouts.admin')
@section('title','Felhasználók')
@section('content')
<h1 class="page-title">Felhasználók</h1>
<div class="container">
    <div class="text-end mb-3">
        <a href="{{route('register.show')}}" class="btn btn-primary">Létrehozás</a>    
    </div>
    <div class="row">
        <div class="col-12"> 
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Felhasználók</h5>
                </div>
                <div class="card-body">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Felhasználó név</th>
                                <th>Email</th>                   
                                <th>Létrehozva</th>                          
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                            <tr>
                                <td>{{$user->id}}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{\Carbon\Carbon::parse($user->created_at)}}</td>
                                <td>
                                    <div class="d-flex" style="gap: 5px">
                                    <a href="{{route('adminpanel.users.user_view',$user->id)}}" class="btn btn-secondary">Részletek</a>
                                    
                                    <form action="{{route('adminpanel.users.user_destroy',$user->id)}}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Törlés</button>
                                    </form>  
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