@extends('layouts.admin')
@section('title','Kategóriák')
@section('content')

<h1 class="page-title">Kategóriák</h1>
<div class="container">
    <div class="row mb-5">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Kategória létrehozása</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('adminpanel.category.store')}}" method="post">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">Név</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{old('name')}}" >
                            @error('name')
                            <span class="invalid-feedback">
                                <strong>{{$message}}</strong>
                            </span>
                            @enderror
                        </div>
                    
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12"> 
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Kategóriák</h5>
                </div>
                <div class="card-body">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Név</th>
                                <th>Összes termék a kategóriában</th>
                                <th>Létrehozva</th>                         
                                <th>Művelet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                            <tr>
                                <td>{{$category->id}}</td>
                                <td>{{$category->name}}</td>
                                <td>{{$products->where('category_id',$category->id)->count()}}</td>
                                <td>{{\Carbon\Carbon::parse($category->created_at)}}</td>
                                <td>
                                    <form action="{{route('adminpanel.category.destroy',$category->id)}}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>         
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