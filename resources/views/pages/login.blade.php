@extends('layouts.master')
@section('title','Login Page')
@section('content')
<section class="login-page bg-light">
    <div class="login-form-box rounded-3 bg-dark text-light">
        <div class="login-title text-light">Bejelentkezés</div>
        <div class="login-form">
            <form action="{{route('login.submit')}}" method="post">
                @csrf
                
                <div class="field">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" class="@error('email') has-error @enderror" placeholder="info@proba.hu">
                    @error('email')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Jelszó</label>
                    <input type="password" id="password" name="password" class="@error('password') has-error @enderror" placeholder="*********">
                    @error('password')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" class="form-check-input"> 
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
                <a href="{{route('password.request')}}"> Elfelejtetted a jelszavad?</a>
            </form>
            
    </div>
</section>
@endsection