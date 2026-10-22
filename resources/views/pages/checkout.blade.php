@extends('layouts.public-v6')
@section('title', 'Rendelés véglegesítése')
@section('content')
<section class="checkout-v6-page">
    <div class="v6-container">
        <header class="checkout-v6-heading">
            <span class="v6-eyebrow">Partneri rendelés</span>
            <h1>Rendelés véglegesítése</h1>
            <p>Ellenőrizd a tételeket, válaszd ki a szállítási és fizetési adatokat, majd add le a rendelést.</p>
        </header>
        @if ($errors->any())
            <div class="shop-v6-alert shop-v6-alert--warning" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <div class="checkout-v6-grid">
            <article class="checkout-v6-panel">
                <div class="checkout-v6-panel-heading"><span class="v6-eyebrow">Kosárösszesítő</span><h2>Rendelt termékek</h2></div>
                <div class="checkout-v6-table-wrap">
                    <table class="checkout-v6-table"><thead><tr><th>Termék</th><th>Csomag</th><th>Egység</th><th>Nettó</th></tr></thead><tbody>
                        @foreach ($lines as $line)
                            @php $quote = $line['quote']; @endphp
                            <tr>
                                <td><strong>{{ $line['product']?->title ?? 'Ismeretlen termék' }}</strong><small>{{ $line['product']?->serial_number }}</small>@if (!$line['available'])<small class="checkout-v6-danger">{{ $line['warning'] }}</small>@endif</td>
                                <td>{{ $line['quantity'] }}</td>
                                <td>{{ $line['pack_quantity'] }}{{ $line['measure'] }}</td>
                                <td>{{ $quote ? number_format($quote['line_net'], 0, ',', ' ') . ' Ft' : 'Nem elérhető' }}</td>
                            </tr>
                        @endforeach
                    </tbody></table>
                </div>
                <dl class="checkout-v6-totals"><div><dt>Nettó</dt><dd>{{ number_format($summary['net'], 0, ',', ' ') }} Ft</dd></div><div><dt>Áfa</dt><dd>{{ number_format($summary['vat'], 0, ',', ' ') }} Ft</dd></div><div class="checkout-v6-total"><dt>Bruttó</dt><dd>{{ number_format($summary['gross'], 0, ',', ' ') }} Ft</dd></div></dl>
            </article>
            <form action="{{ route('validcheckout') }}" id="payment-form" method="post" class="checkout-v6-panel checkout-v6-form">
                @csrf
                <input type="hidden" name="checkout_token" value="{{ $checkout_token }}">
                <div class="checkout-v6-panel-heading"><span class="v6-eyebrow">Véglegesítés</span><h2>Szállítás és fizetés</h2></div>
                <label for="payment_method">Fizetési mód<select name="payment_method" id="payment_method" required><option value="0" @selected(old('payment_method', '0') === '0')>Átutalás</option><option value="1" @selected(old('payment_method') === '1')>Készpénz</option></select></label>
                @if (auth()->user()->user_shipping->count())
                    <label for="shipping_address_id">Szállítási cím<select name="shipping_address_id" id="shipping_address_id" required>@foreach(auth()->user()->user_shipping as $address)<option value="{{ $address->id }}" @selected((string) old('shipping_address_id', $user_s?->id) === (string) $address->id)>{{ $address->zipcode }} {{ $address->city }}, {{ $address->address }} ({{ $address->receiver }})</option>@endforeach</select></label>
                @else
                    <div class="shop-v6-alert shop-v6-alert--warning">Nincs megadott szállítási címed. <a href="{{ route('shipping.create') }}">Adj hozzá egyet.</a></div>
                @endif
                <label class="checkout-v6-check"><input type="checkbox" name="is_direct_shipping" value="1" @checked(old('is_direct_shipping'))><span>Árazatlan szállító, közvetlen partneri szállítással</span></label>
                <label for="note">Megjegyzés<textarea name="note" id="note" rows="4" maxlength="500">{{ old('note') }}</textarea></label>
                <label class="checkout-v6-check"><input type="checkbox" id="terms" required><span>Elfogadom az <a href="{{ route('aszf') }}">ÁSZF-et</a> és az <a href="{{ route('adatkezelesi-nyilatkozat') }}">Adatkezelési nyilatkozatot</a>.</span></label>
                @if ($summary['has_unavailable'])<div class="shop-v6-alert shop-v6-alert--warning">A nem elérhető tételeket távolítsd el a kosárból a rendelés előtt.</div>@endif
                <button type="submit" class="v6-button v6-button--primary checkout-v6-submit" @disabled($summary['has_unavailable'] || !auth()->user()->user_shipping->count())>Rendelés leadása</button>
            </form>
        </div>
    </div>
</section>
@endsection
