

@extends('pages.account')
@section('title','Adatok szerkesztése')
@section('content')
{{-- javascript --}}
<script>
    $(document).ready(function(){
        $('#zipcode').on('input',function(e){
            var zip= $('#zipcode').val();
            if(zip.length==4){
                
            }
        });
        $('#vatnumber').change(function(){
            
        
    }); 
    
    });
    
</script>
<div class="accounts-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-12">
                @include('pages.components.account.header')
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12">
                @if(session('success'))
                <div class="alert alert-success">
                    {{session('success')}}
                </div>
                @endif
                <section class="account-modify-box">
                    <p class="account-modify-box-title ">
                        Felhasználó adatok módosítása
                        @if($errors->any())
    {!! implode('', $errors->all('<div>:message</div>')) !!}
@endif
                    </p>
                    <div class="row">
                        <form action="{{route('account.edit')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                        <div class="user-information">
                            <p class="account-modify-box-subtitle">Felhasználói adatok:<p>
                                <div class="row mb-1">
                                    <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="username">Felhasználó név</label>
                                        <input type="text" name="username" id="username" class="form-control  @error('username') is-invalid @enderror" value="{{auth()->user()->name}}" required>
                                        @error('username')
                                        <span class="invalid-feedback">
                                            <strong>{{$message}}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="email">Email</label>
                                            <input type="text" name="email" id="email" class="form-control  @error('email') is-invalid @enderror" value="{{auth()->user()->email}}" required>
                                            @error('email')
                                            <span class="invalid-feedback">
                                                <strong>{{$message}}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        </div>
                                                                    
                                </div>
                            <div class="row mb-1">
                                <div class="col-12 text-left">
                                    <a href="{{route('change-password')}}">Jelszó megváltoztatása</a>
                                </div>
                            </div>
                        </div>
                        <div class="invoice-information">
                            <p class="account-modify-box-subtitle">Számlázási adatok:<p>
                                <div class="row mb-1">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="company_name">Cégnév</label>
                                            <input type="text" name="company_name" id="company_name" class="form-control  @error('company_name') is-invalid @enderror" value="{{auth()->user()->user_invoice->company_name  ?? ""}}" required>
                                            @error('company_name')
                                            <span class="invalid-feedback">
                                                <strong>{{$message}}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="vatnumber">Adószám</label>
                                            <input type="text" ma name="vatnumber"  title="xxxxxxxx-y-zz" id="vatnumber" minlength="13" maxlength="13" class="form-control  @error('vatnumber') is-invalid @enderror" value="{{auth()->user()->user_invoice->vatnumber ?? ""}}" required>
                                            @error('vatnumber')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror                                   
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="country">Ország</label>
                                            <select name="country" id="country" class="form-control">
                                                <option value="Magyarország" >Magyarország</option>
                                            </select>

                                        </div>
                                        @error('country')
                                        <span class="invalid-feedback">
                                            <strong>{{$message}}</strong>
                                        </span>
                                        @enderror 
                                        
                                        
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="zipcode">Irányítószám</label>
                                            <input type="text" name="zipcode" id="zipcode" maxlength="4" class="form-control  @error('zipcode') is-invalid @enderror" value="{{auth()->user()->user_invoice->zipcode ?? ""}}" required>
                                            @error('zipcode')
                                            <span class="invalid-feedback">
                                                <strong>{{$message}}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="state">Megye</label>
                                            <input type="text" name="state" id="state" class="form-control @error('state') is-invalid @enderror" value="{{auth()->user()->user_invoice->state ?? ""}}" required>
                                            @error('state')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                        </div>                                        
                                    </div>
                                    
                                </div>
                                <div class="row mb-1">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="city">Város</label>
                                            <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{auth()->user()->user_invoice->city ?? ""}}" required>
                                            @error('city')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group mb-3">
                                            <label for="address">Cím</label>
                                            <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{auth()->user()->user_invoice->address ?? ""}}" required>
                                            @error('address')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror    
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="shipping-information">
                            <p class="account-modify-box-subtitle">Kiszállítási adatok:<p>
                                <div class="row mb-1">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="receiver">Átvevő</label>
                                            <input type="text" name="receiver" id="receiver"
                                             class="form-control @error('receiver') is-invalid @enderror"
                                              value="{{auth()->user()->user_shipping->receiver ?? ""}}" required>
                                            @error('receiver')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror    
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="phone">Átvevő telefonszáma</label>
                                            <input type="text" name="phone" title="+36301234567" maxlength="12" id="phone" class="form-control 
                                            @error('phone') is-invalid @enderror" 
                                            value="{{auth()->user()->user_shipping->phone ?? ""}}" required>
                                            @error('phone')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror    
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    
                                        
                                    <div class="col-md-2">
                                        <div class="form-group mb-3">
                                            <label for="s_zipcode">Irányítószám</label>
                                            <input type="text" name="s_zipcode" id="s_zipcode" pattern="\d{4,4}" class="form-control @error('s_zipcode') is-invalid @enderror" value="{{auth()->user()->user_shipping->zipcode ?? ""}}" required>
                                            @error('s_zipcode')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="s_city">Város</label>
                                            <input type="text" name="s_city" id="s_city" class="form-control " value="{{auth()->user()->user_shipping->city ?? ""}}" required>
                                            @error('s_city')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group mb-3">
                                            <label for="s_address">Cím</label>
                                            <input type="text" name="s_address" id="s_address" class="form-control @error('s_address') is-invalid @enderror" value="{{auth()->user()->user_shipping->address ?? ""}}" required>
                                            @error('s_address')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror    
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label for="comment">Megjegyzés a futárnak</label>
                                            <input type="text" name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror" value="{{auth()->user()->user_shipping->comment ?? ""}}" required>
                                            @error('comment')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" required>
                                            <label class="form-check-label" for="flexCheckDefault">
                                                Elolvastam és elfogadom az <a href="{{route('aszf')}}"> ÁSZF-et</a> és  az <a href="{{route('adatkezelesi-nyilatkozat')}}">Adatkezelési nyilatkozatot</a>.
                                            </label>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    <div class="form-group my-3">
                        {{-- <label for="password">Jelszó</label>
                        <input type="password" name="password" id="password" style="width:350px" class="form-control @error('comment') is-invalid @enderror">
                        @error('password')
                                    <span class="invalid-feedback">
                                        <strong>{{$message}}</strong>
                                    </span>
                                    @enderror --}}
                        <button id="update_btn" type="submit" class="btn btn-primary bg-gradient mt-3">Módosítás</button>
                    </div>
                    </form>
                </section>
            </div>
        </div>
        
        
    </div>
</div>

@endsection