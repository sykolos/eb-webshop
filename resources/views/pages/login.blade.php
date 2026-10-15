@extends('layouts.master')
@section('title','Partner belépés')
@section('content')
<section class="login-page bg-light">
    <div class="login-form-box rounded-3 bg-dark text-light">
        <div class="login-title text-light">Partner belépés</div>
        <div class="login-form">
            @if(session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            <form action="{{route('login.submit')}}" method="post">
                @csrf
                
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" class="@error('email') has-error @enderror" placeholder="info@proba.hu" required>
                    @error('email')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Jelszó</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" class="@error('password') has-error @enderror" placeholder="*********" required>
                    @error('password')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input" @checked(old('remember'))>
                    <label class="form-check-label" for="remember">
                    Maradjak bejelentkezve
                    </label>
                    @error('remember')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="field">
                    <button type="submit" class="btn btn-primary bg-gradient btn-block">Bejelentkezés</button>
                </div>
                <div class="d-flex flex-column gap-2 mt-3">
                    <a href="{{route('password.request')}}">Elfelejtetted a jelszavad?</a>
                    <a href="{{ route('register') }}">Partnerfiók igénylése</a>
                </div>
            </form>
            
    </div>
</section>
@endsection
