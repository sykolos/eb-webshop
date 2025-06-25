@extends('layouts.admin')
@section('title','Termék módosítás')
@section('content')
<h1 class="page-title">Termék módosítás</h1>

<div class="container">
    <div class="row mb-5">
        <div class="col-12
        ">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Termék módosítás</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('adminpanel.products.edit',$product->id)}}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="title">Megnevezés</label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{$product->title}}" >
                                    @error('title')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label for="price">Ár</label>
                                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{$product->price }}" >
                                    @error('price')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="serialnum">Cikkszám</label>
                                    <input type="text" name="serialnum" id="serialnum" class="form-control @error('serialnum') is-invalid @enderror" value="{{$product->serial_number}}" >
                                    @error('serialnum')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Kategória</label>
                                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                    <option value="">--- Select category ---</option>
                                    @foreach ($categories as $category)
                                        <option value="{{$category->id}}" {{$product->category_id==$category->id ? 'selected' : ''}}> {{$category->name}} </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <span class="invalid-feedback">
                                    <strong>{{$message}}</strong>
                                </span>
                                @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Units">Mennyiségi egység</label>
                                    <select name="unit_id" id="unit_id" class="form-control @error('unit_id') is-invalid @enderror" >
                                        @foreach($product_units as $unit)
                                        <option value="{{$unit->id}}" {{$product->unit_id==$unit->id ? 'selected' : ''}}>{{$unit->unit}}({{$unit->quantity}}{{$unit->measure}})</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                    
                                </div>
                            </div>
                         </div>  
                         <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Leírás</label>
                                    <textarea placeholder="Describe your product" name="description" id="description" cols="30" rows="10" class="form-control @error('description') is-invalid @enderror">{{$product->description}}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                           <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">Kép</label>
                                    <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                                    @error('image')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                    <img src="{{asset('storage/public/'.$product->image)}}" alt="" width="100px" height="100px" class=" my-3">
                                </div>
                            </div>
                       
                        
                        </div>

                        
                    
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-primary">Frissítés</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
