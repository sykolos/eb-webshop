@extends('layouts.public-v6')
@section('title', 'Rendelés véglegesítése')
@section('content')

<div class="row justify-content-center align-items-center page-header-title">
    <div class="col-md-6 text-center mb-5">
        <h2 class="heading-section section-header">Rendelés véglegesítése</h2>
    </div>
</div>

<main class="checkout-page">
    <div class="container">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card p-4">
                    <h3 class="h4 mb-3">Termékösszesítő</h3>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr><th>Termék</th><th>Csomag</th><th>Egység</th><th class="text-end">Nettó</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($lines as $line)
                                    @php $quote = $line['quote']; @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $line['product']?->title ?? 'Ismeretlen termék' }}</strong>
                                            <small class="d-block text-muted">{{ $line['product']?->serial_number }}</small>
                                            @if (!$line['available'])<small class="text-danger">{{ $line['warning'] }}</small>@endif
                                        </td>
                                        <td>{{ $line['quantity'] }}</td>
                                        <td>{{ $line['pack_quantity'] }}{{ $line['measure'] }}</td>
                                        <td class="text-end">{{ $quote ? number_format($quote['line_net'], 0, ',', ' ') . ' Ft' : 'Nem elérhető' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-top pt-3 text-end">
                        <div>Nettó: <strong>{{ number_format($summary['net'], 0, ',', ' ') }} Ft</strong></div>
                        <div>Áfa: <strong>{{ number_format($summary['vat'], 0, ',', ' ') }} Ft</strong></div>
                        <div class="fs-5">Bruttó: <strong>{{ number_format($summary['gross'], 0, ',', ' ') }} Ft</strong></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <form action="{{ route('validcheckout') }}" id="payment-form" method="post" class="card p-4">
                    @csrf
                    <input type="hidden" name="checkout_token" value="{{ $checkout_token }}">
                    <h3 class="h4 mb-3">Szállítás és véglegesítés</h3>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Fizetési mód</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="0" @selected(old('payment_method', '0') === '0')>Átutalás</option>
                            <option value="1" @selected(old('payment_method') === '1')>Készpénz</option>
                        </select>
                    </div>

                    @if (auth()->user()->user_shipping->count())
                        <div class="mb-3">
                            <label for="shipping_address_id" class="form-label">Szállítási cím</label>
                            <select name="shipping_address_id" id="shipping_address_id" class="form-control" required>
                                @foreach(auth()->user()->user_shipping as $address)
                                    <option value="{{ $address->id }}" @selected((string) old('shipping_address_id', $user_s?->id) === (string) $address->id)>
                                        {{ $address->zipcode }} {{ $address->city }}, {{ $address->address }} ({{ $address->receiver }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="alert alert-warning">Nincs megadott szállítási címed. <a href="{{ route('shipping.create') }}">Adj hozzá egyet.</a></div>
                    @endif

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_direct_shipping" name="is_direct_shipping" value="1" @checked(old('is_direct_shipping'))>
                        <label class="form-check-label" for="is_direct_shipping">Árazatlan szállító, közvetlen partneri szállítással</label>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Megjegyzés</label>
                        <textarea name="note" id="note" class="form-control" rows="4" maxlength="500">{{ old('note') }}</textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">Elfogadom az <a href="{{ route('aszf') }}">ÁSZF-et</a> és az <a href="{{ route('adatkezelesi-nyilatkozat') }}">Adatkezelési nyilatkozatot</a>.</label>
                    </div>

                    @if ($summary['has_unavailable'])
                        <div class="alert alert-danger">A nem elérhető tételeket távolítsd el a kosárból a rendelés előtt.</div>
                    @endif
                    <button type="submit" class="btn btn-primary btn-lg" @disabled($summary['has_unavailable'] || !auth()->user()->user_shipping->count())>Rendelés leadása</button>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
