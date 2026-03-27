@extends('layouts.admin')
@section('title','Termék feltöltés')
@section('content')
<?php
// Sort categories and units alphabetically
$sortedCategories = collect($categories)->sortBy('name');
$sortedUnits = collect($product_units)->sortBy('unit');
?>
<h1 class="page-title">Termék feltöltés</h1>
<div class="container">
    <div class="row mb-5">
        <div class="col-12">
            <a href="{{ route('adminpanel.products') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-secondary mb-3">
                ← Vissza a termékekhez
            </a>
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Termék feltöltés</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('adminpanel.products.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="title">Megnevezés</label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{old('title')}}" >
                                    @error('title')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price">Ár</label>
                                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{old('price')}}" >
                                    @error('price')
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
                                    <select name="category_id" id="category_id" class="form-select select2-search @error('category_id') is-invalid @enderror">
                                    <option value="">--- Select category ---</option>
                                    @foreach ($sortedCategories as $category)
                                        <option value="{{$category->id}}" {{old('category_id')==$category->id ? 'selected' : ''}}> {{$category->name}} </option>
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
                                    <label for="image">Kép</label>
                                    <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                                    @error('image')
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
                                    <label for="colors">Mennyiségi egység</label>
                                    <select name="unit_id" id="unit_id" class="form-select select2-search @error('unit_id') is-invalid @enderror" >
                                        @foreach($sortedUnits as $unit)
                                        <option value="{{$unit->id}}" {{old('unit_id')==$unit->id ? 'selected' : ''}}>{{$unit->unit}}({{$unit->quantity}}{{$unit->measure}})</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="serialnum">Cikkszám</label>
                                    <input type="text" name="serialnum" id="serialnum" class="form-control @error('serialnum') is-invalid @enderror" value="{{old('serialnum')}}" >
                                    @error('serialnum')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Leírás</label>
                                    <textarea placeholder="Termék leírása" name="description" id="description" cols="30" rows="10" class="form-control @error('description') is-invalid @enderror"></textarea>
                                    @error('description')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        
                    
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-primary">Feltöltés</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if (typeof $.fn.select2 === 'function') {
        // Initialize Select2 for category and unit selects
        $('#category_id, #unit_id').select2({
            allowClear: true,
            theme: 'bootstrap-5',
            language: 'hu',
            width: '100%',
            placeholder: 'Keresés...'
        });
    } else {
        console.warn('Select2 not loaded; skipping select2 initialization.');
    }
});
</script>
@endsection