@extends('layouts.v6.shop')
@section('title', 'Kapcsolat')
@section('content')
<div class="v6-container v6-container--narrow v6-m14-page">
    <div class="v6-section__header"><div><span class="v6-eyebrow">KAPCSOLAT</span><h1>Beszéljünk a beszerzésről.</h1><p>Közvetlen segítség termékekhez, partnerárakhoz és szállításhoz.</p></div></div>
    @if(session('success'))<div class="v6-toast" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="v6-toast v6-toast--warning" role="alert">{{ $errors->first() }}</div>@endif
    <div class="v6-m14-contact-grid">
        <section class="v6-panel v6-panel--dark"><h2>Közvetlenül elérsz minket.</h2><p>Munkanapokon {{ config('site.company.opening_hours') }}.</p><p><strong>Telephely</strong><br>{{ config('site.company.address') }}</p><p><strong>Email</strong><br><a href="mailto:{{ config('site.company.email') }}">{{ config('site.company.email') }}</a><br><a href="mailto:{{ config('site.company.secondary_email') }}">{{ config('site.company.secondary_email') }}</a></p><p><strong>Telefon</strong><br>@foreach(config('site.company.phones') as $phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a><br>@endforeach</p></section>
        <form class="v6-form" method="POST" action="{{ route('sendContact') }}">@csrf
            <div class="v6-form-grid"><label>Név<input name="name" value="{{ old('name') }}" required maxlength="255"></label><label>Email<input type="email" name="email" value="{{ old('email') }}" required maxlength="255"></label><label>Tárgy<input name="subject" value="{{ old('subject') }}" maxlength="191"></label><label class="v6-field--full">Üzenet<textarea name="message" required maxlength="5000" rows="7">{{ old('message') }}</textarea></label><input class="m14-honeypot" name="website" tabindex="-1" autocomplete="off"></div>
            <button class="v6-button v6-button--primary" type="submit">Üzenet küldése</button>
        </form>
    </div>
</div>
@endsection
