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
                    {{--  Asztali nézet – táblázat --}}
                        <div class="d-none d-lg-block">
                            <table class="table table-striped align-middle">
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
                                        <td>{{ $category->id }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $products->where('category_id', $category->id)->count() }}</td>
                                        <td>{{ \Carbon\Carbon::parse($category->created_at)->format('Y-m-d') }}</td>
                                        <td>
                                            <form action="{{ route('adminpanel.category.destroy', $category->id) }}" method="post" onsubmit="return confirm('Biztosan törlöd?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Törlés
                                                </button>
                                            </form>         
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{--  Mobil nézet – kártyák --}}
                        <div class="d-block d-lg-none">
                            <div class="row row-cols-1 g-3">
                                @foreach ($categories as $category)
                                <div class="col">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $category->name }}</h5>
                                            <p class="mb-1"><strong>Kategória ID:</strong> {{ $category->id }}</p>
                                            <p class="mb-1"><strong>Termékek száma:</strong> {{ $products->where('category_id', $category->id)->count() }}</p>
                                            <p class="mb-1"><strong>Létrehozva:</strong> {{ \Carbon\Carbon::parse($category->created_at)->format('Y-m-d') }}</p>
                                            
                                            <form action="{{ route('adminpanel.category.destroy', $category->id) }}" method="post" onsubmit="return confirm('Biztosan törlöd?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100 mt-2">
                                                    <i class="bi bi-trash"></i> Törlés
                                                </button>
                                            </form>  
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection