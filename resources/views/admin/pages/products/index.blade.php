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
                <form method="GET" class="mb-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Keresés név alapján" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="min_price" class="form-control" placeholder="Min ár" value="{{ request('min_price') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="max_price" class="form-control" placeholder="Max ár" value="{{ request('max_price') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="category_id" class="form-select">
                                <option value="">-- Kategória --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @if(request('category_id') == $category->id) selected @endif>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
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
                                <td><img src="{{ Storage::url($product->image) }}" style="height:40px" alt="{{ $product->title }}" loading="lazy"></td>
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
        <div class="d-flex justify-content-center mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection