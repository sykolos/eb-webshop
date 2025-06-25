@extends('layouts.admin')
@section('title','Külön árak')
@section('content')
<h1 class="page-title">Felhasználók árai</h1>
<div class="container spec-prices">
    {{-- <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="colors">Mennyiségi egység</label>
                <select name="unit_id" id="unit_id" class="form-control @error('unit_id') is-invalid @enderror" >
                    @foreach($product_units as $unit)
                    <option value="{{$unit->id}}" {{old('unit_id')==$unit->id ? 'selected' : ''}}>{{$unit->unit}}</option>
                    @endforeach
                </select>
                @error('unit_id')
                <span class="invalid-feedback">
                    <strong>{{$message}}</strong>
                </span>
                @enderror
                
            </div>
        </div>
    </div> --}}
    <div class="row mb-3">
        
        <div class="col-md-3">
            <div class="form-group">
                
            <select name="users" id="users" class="form-control">
                <option value="everyone">---</option>
                @foreach ($users as $item)
                    <option value="{{route('adminpanel.special_price_check',$item->id)}}" @if($listaok && $item->id==$id) selected @endif>{{$item->name}}</option>
                @endforeach
            </select>
            
            </div>
        </div>
    
    </div>
    <script>
        $('select').on('change', function (e) {
            var link = $("option:selected", this).val();
            if (link) {
                location.href = link;
            }
        });
    </script>
    {{-- @if($cserevan)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Termék</h5>
                </div>
                <div class="card-body">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>Megnevezés</th>
                                <th>Alap ár</th>
                                <th>Jelenlegi ár</th>
                                <th>Új ár</th>                                                     
                                
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach($csereproduct as $item)
                                <td>{{$item->id}}</td>
                                <td>{{$item->name}}</td>
                                <td>{{App\Models\Products::where('id','=',$item->id)->first()->price}}</td>
                                <td>{{$item->price}}</td>
                                <td>
                                    <form action="{{route('adminpanel.special_price_set',['id' => $id, 'pid' => $item->id])}}" method="post">
                                    @csrf
                                    <input type="text" name="price" id="price">
                                    <button type="submit" class="btn btn-danger">Csere</button>

                                </form>
                                
                                
                                    @endforeach
                            </tr>
                        </tbody>
                    </table>
                                    
                </div>
            </div>
        </div>
    </div>
    @endif --}}
    @if($listaok)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Termék</h5>
                </div>
                <div class="card-body">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>Cikkszám</th>
                                <th>Megnevezés</th>
                                <th>Alap ár</th>
                                <th>Jelenlegi ár</th>
                                <th>Új ár</th>                                                     
                                
                            </tr>
                        </thead>
                        <tbody>
                            @if($cserevan)
                            <tr>
                                @foreach($csereproduct as $item)
                                <td>{{$item->id}}</td>
                                <td>{{$item->serial}}</td>
                                <td>{{$item->name}}</td>
                                <td>{{App\Models\Products::where('id','=',$item->id)->first()->price}}</td>
                                <td>{{$item->price}}</td>
                                <td>
                                    <form action="{{route('adminpanel.special_price_set',['id' => $id, 'pid' => $item->id])}}" method="post">
                                    @csrf
                                    <input type="text" name="price" id="price">
                                    <button type="submit" class="btn btn-danger">Csere</button>

                                </form>
                                
                                
                                    @endforeach
                            </tr>
                            @endif
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
                    <h5>{{$users->find($id)->name}} Árai</h5>
                </div>
                <div class="card-body">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Cikkszám</th>
                                <th>Megnevezés</th>
                                <th>Alap ár</th>      
                                <th>Jelenlegi ár</th>                                 
                                <th>Szerkesztés</th> 
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $item)
                            <tr>
                                <td>{{$item->id}}</td>
                                <td>{{$item->serial}}</td>
                                <td>{{$item->name}}</td>
                                <td>{{App\Models\Products::where('id','=',$item->id)->first()->price}}</td>
                                <td>{{$item->price}}
                                
                                </td>
                                <td>
                                    <div class="d-flex" style="gap: 5px">
                                    <a href="{{route('adminpanel.special_price_change',['id' => $id, 'pid' => $item->id])}}" class="btn btn-secondary">Csere</a>
                                    
                                    <form action="{{route('adminpanel.special_price_destroy',['id' => $id, 'pid' => $item->id])}}" method="post">
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
    @endif
</div>
@endsection
