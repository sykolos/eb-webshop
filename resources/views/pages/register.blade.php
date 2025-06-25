@extends('layouts.master')
@section('title','Register Page')
@section('content')
<section class="login-page">
    <div class="login-form-box">
        <div class="login-title">Register</div>
        <div class="login-form">
            <form action="{{route('register.submit')}}" method="post">
                @csrf
                <div class="field">
                    <label for="name">Felhasználónév</label>
                    <input type="text" id="name" name="name" class="@error('name') has-error @enderror" placeholder="Próba János">
                    @error('name')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>
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
                <div class="field">
                    <label for="password-confirm">Jelszó újra</label>
                    <input type="password" id="password-confirm" name="password-confirm" placeholder="*********">
                </div>
                <div class="field">
                    <button type="submit" class="btn btn-primary btn-block">Regisztráció</button>
                </div>
            </form>
            
            
    </div>
</section>
@endsection