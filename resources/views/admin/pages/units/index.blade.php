@extends('layouts.admin')
@section('title','Egységek')
@section('content')

<h1 class="page-title">Termék egységek</h1>
<div class="container">
    <div class="row mb-5">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Termék egységek hozzáadása</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('adminpanel.units.unit_store')}}" method="post">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="unit">Neve</label>
                            <input type="text" name="unit" id="unit" class="form-control @error('unit') is-invalid @enderror" value="{{old('unit')}}" >
                            @error('unit')
                            <span class="invalid-feedback">
                                <strong>{{$message}}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="quantity">Mennyiség</label>
                            <input type="text" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{old('quantity')}}" >
                            @error('quantity')
                            <span class="invalid-feedback">
                                <strong>{{$message}}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="measure">Mértékegység</label>
                            <input type="text" name="measure" id="measure" class="form-control @error('measure') is-invalid @enderror" value="{{old('measure')}}" >
                            @error('measure')
                            <span class="invalid-feedback">
                                <strong>{{$message}}</strong>
                            </span>
                            @enderror
                        </div>
                    
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-primary">Létrehoz</button>
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
                    <h5>Létrehozott egységek</h5>
                </div>
                <div class="card-body">
                    {{--  Asztali nézet – táblázat --}}
                    <div class="d-none d-lg-block table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Neve</th>
                                    <th>Mennyiség</th>                                
                                    <th>Mértékegység</th>
                                    <th>Létrehozva</th>                         
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($units as $unit)
                                <tr>
                                    <td>{{ $unit->id }}</td>
                                    <td>{{ $unit->unit }}</td>
                                    <td>{{ $unit->quantity }}</td>
                                    <td>{{ $unit->measure }}</td>
                                    <td>{{ \Carbon\Carbon::parse($unit->created_at)->format('Y-m-d') }}</td>
                                    <td>
                                        <form action="{{ route('adminpanel.units.unit_destroy', $unit->id) }}" method="post" onsubmit="return confirm('Biztosan törlöd?');">
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
                            @foreach ($units as $unit)
                            <div class="col">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $unit->unit }}</h5>
                                        <p class="mb-1"><strong>Mennyiség:</strong> {{ $unit->quantity }}</p>
                                        <p class="mb-1"><strong>Mértékegység:</strong> {{ $unit->measure }}</p>
                                        <p class="mb-1"><strong>Létrehozva:</strong> {{ \Carbon\Carbon::parse($unit->created_at)->format('Y-m-d') }}</p>

                                        <form action="{{ route('adminpanel.units.unit_destroy', $unit->id) }}" method="post" onsubmit="return confirm('Biztosan törlöd?');" class="mt-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">
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