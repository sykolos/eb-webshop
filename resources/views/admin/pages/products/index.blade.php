@extends('layouts.admin')
@section('title','Termékek')
@section('content')
<h1 class="page-title">Termékek</h1>
<div class="container">
    <div class="text-end mb-3">
        <a href="{{route('adminpanel.products.create')}}" class="btn btn-primary">Create Product</a>    </div>
    <div class="row">
        <div class="col-12"> 
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Termékek</h5>
                </div>
                <div class="card-body">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Cikkszám</th>
                                <th>Termékneve</th>
                                <th>Egység ár</th>
                                <th>Menny.egység</th>
                                <th>Kategória</th>     

                                {{-- <th>Colors</th>                          --}}
                                <th>Kép</th>                       
                                <th>Létrehozva</th>                          
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                            <tr>
                                <td>{{$product->id}}</td>
                                <td>{{$product->serial_number}}</td>
                                <td>{{$product->title}}</td>
                                <td>{{$product->price}}</td>
                                <td>
                                    @foreach ($units as $unit)
                                        @if($unit->id==$product->unit_id)
                                        {{$unit->unit}}({{$unit->quantity}} {{$unit->measure}})
                                        @endif
                                    @endforeach
                                </td>
                                <td>{{$product->category->name}}
                                </td>
                                {{-- <td>
                                    @foreach($product->colors as $color)
                                        <span class="badge"style="background:{{$color->code}}">{{$color->name}}</span>
                                    @endforeach
                                </td> --}}
                                <td><img src="{{asset('storage/public/'.$product->image)}}" style="height:40px" alt=""></td>
                                <td>{{\Carbon\Carbon::parse($product->created_at)}}</td>
                                <td>
                                    <div class="d-flex" style="gap: 5px">
                                    <a href="{{route('adminpanel.products.edit',$product->id)}}" class="btn btn-secondary">Edit</a>
                                    
                                    <form action="{{route('adminpanel.products.destroy',$product->id)}}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
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