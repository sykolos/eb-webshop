@extends('layouts.master')
@section('title','Sikeres Rendelés!')
@section('content')
<section class="status-v6-page">
    <div class="v6-container v6-container--narrow">
        <span class="v6-eyebrow">Rendelés rögzítve</span>
        <h1>Sikeres rendelés</h1>
        <p class="status-v6-page__lead">A rendelésedet rögzítettük. A visszaigazolás és a rendelés részletei a fiókodban is elérhetők.</p>
        <p class="status-v6-page__number">Rendelésazonosító: <strong>{{ $order->displayNumber() }}</strong></p>
        <a href="{{ route('account.orders') }}" class="v6-button v6-button--primary">Rendeléseim</a>
    </div>
</section>
@endsection
