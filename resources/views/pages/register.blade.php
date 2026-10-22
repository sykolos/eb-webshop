@extends('layouts.public-v6')
@section('title','Partnerfiók igénylése')
@section('content')
<section class="login-page">
    <div class="login-form-box">
        <div class="login-title">Partnerfiók igénylése</div>
        <div class="login-form">
            <p class="text-muted">A kérelmet jóváhagyás után aktiváljuk. A megadott email címen értesítünk a következő lépésről.</p>
            <form action="{{route('register.submit')}}" method="post">
                @csrf
                <div class="field">
                    <label for="name">Felhasználónév</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" class="@error('name') has-error @enderror" placeholder="Próba János" required>
                    @error('name')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" class="@error('email') has-error @enderror" placeholder="info@proba.hu" required>
                    @error('email')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">Jelszó</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" class="@error('password') has-error @enderror" placeholder="*********" required>
                    @error('password')
                        <span class="field-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="password-confirm">Jelszó újra</label>
                    <input type="password" id="password-confirm" name="password_confirmation" autocomplete="new-password" placeholder="*********" required>
                </div>
                <div class="field">
                    <label for="company_name">Cégnév</label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" class="@error('company_name') has-error @enderror" required>
                    @error('company_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="vatnumber">Adószám</label>
                    <input type="text" id="vatnumber" name="vatnumber" value="{{ old('vatnumber') }}" class="@error('vatnumber') has-error @enderror">
                    @error('vatnumber')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="phone">Telefonszám</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="@error('phone') has-error @enderror">
                    @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="message">Megjegyzés</label>
                    <textarea id="message" name="message" rows="3" class="@error('message') has-error @enderror">{{ old('message') }}</textarea>
                    @error('message')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <button type="submit" class="btn btn-primary btn-block">Kérelem beküldése</button>
                </div>
                <a href="{{ route('login') }}">Már van partnerfiókod? Belépés</a>
            </form>
            
            
    </div>
</section>
@endsection
