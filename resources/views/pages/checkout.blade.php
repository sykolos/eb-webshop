@extends('layouts.master')
@section('title','Checkout Page')
@section('content')

<div class="row justify-content-center align-items-center page-header-title">
    <div class="col-md-6 text-center mb-5">
        <h2 class="heading-section section-header ">Véglegesités</h2>
    </div>
</div>

<main class="checkout-page">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-12">
                <div class="wrapper bg-dark rounded-3 ">
                    <div class="row no-gutters mb-5 checkout-row">
                        <div class="col-md-5 left-side">
                            <div class="contact-leftside-wrap w-100 p-md-5 p-4">
                                    <h4 class="mb-4 text-light">Megrendelő adatai</h4>
                                    
                                    <div class="row">
                                            <div class="col-md-12">
                                                <ul class="list-group list-group-flush bg-dark">
                                                    <li class="list-group-item bg-dark text-white d-flex">Megrendelő:&nbsp;&nbsp;&nbsp;{{$user_i->company_name}}</li>
                                                    <li class="list-group-item bg-dark text-white">Számlázási cím:&nbsp;&nbsp;&nbsp;{{$user_i->address}}</li>
                                                    <li class="list-group-item bg-dark text-white">Adószám:&nbsp;&nbsp;&nbsp;{{$user_i->vatnumber}}</li>                                                    
                                                    <li class="list-group-item bg-dark text-white">Kiszállítási cím:&nbsp;&nbsp;&nbsp;<span id="preview-address"></span></li>
                                                    <li class="list-group-item bg-dark text-white">Átvevő:&nbsp;&nbsp;&nbsp;<span id="preview-receiver"></span></li>
                                                    <li class="list-group-item bg-dark text-white">Elérhetőség:&nbsp;&nbsp;&nbsp;<span id="preview-phone"></span></li>
                                                </ul>
                                            </div>
                                        </div>
                            </div>
                        </div>
                        
                        <div class="col-md-7 d-flex bg-light right-side">
                            <div class="cart-mini w-100 p-md-5 p-4">
                            <h3 class="mb-4">Termék összesítő</h3>
                            <table class="table ">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Termék</th>
                                        <th>Egység ár</th>
                                        <th>Mennyiség</th>
                                        <th>M.egység</th>
                                        <th>Összesen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(session()->has('cart') && count(session()->get('cart')) > 0)
                                        @foreach (session()->get('cart') as $key => $item)
                                            <tr>
                                                <td>
                                                    <p>{{ $item['product']['title'] }}</p>
                                                </td>
                                                <td>
                                                    {{ App\Models\Products::with('special_prices')->find($item['product']['id'])->getPriceForUser() }} Ft
                                                </td>
                                                <td>
                                                    {{ $item['quantity'] * $item['q'] }}
                                                </td>
                                                <td>
                                                    {{ $item['m'] }}
                                                </td>
                                                <td>
                                                    {{ number_format(App\Models\Cart::unitprice($item) * $item['q']) }} Ft
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- Összesítés --}}
                                        <tr class="cart-total">
                                            <td colspan="4" class="text-end fw-bold">Összesen nettó:</td>
                                            <td>{{ number_format(App\Models\Cart::totalamount(), 0, '', ' ') }} Ft</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Bruttó végösszeg:</td>
                                            <td>{{ number_format(App\Models\Cart::gettotalvatprice(), 0, '', ' ') }} Ft</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Áfa összesen:</td>
                                            <td>{{ number_format(App\Models\Cart::gettotalvat(), 0, '', ' ') }} Ft</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="5" class="empty-cart text-center">Üres a kosarad</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <form action="{{ route('validcheckout') }}" id="payment-form" method="post">
                    @csrf
                    <div class="row">
                        {{-- Bal oszlop --}}
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="payment_method">Fizetési mód:</label>
                                <select name="payment_method" id="payment_method" class="form-control" required>
                                    <option value="0">Átutalás</option>
                                    <option value="1">Készpénz</option>
                                </select>
                            </div>
                            @if(auth()->user()->user_shipping && auth()->user()->user_shipping->count())
                                <div class="form-group mb-3">
                                    <label for="shipping_address_id">Válaszd ki a szállítási címet:</label>
                                    <select name="shipping_address_id" id="shipping_address_id" class="form-control" required onchange="updateShippingPreview(this)">
                                        @foreach(auth()->user()->user_shipping as $address)
                                            <option 
                                                value="{{ $address->id }}"
                                                data-address="{{ $address->address }}"
                                                data-receiver="{{ $address->receiver }}"
                                                data-phone="{{ $address->phone }}"
                                            >
                                                {{ $address->zipcode }} {{ $address->city }}, {{ $address->address }} ({{ $address->receiver }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    Nincs megadott szállítási címed. <a href="{{ route('shipping.create') }}">Adj hozzá egyet itt.</a>
                                </div>
                            @endif
                            <div class="form-group form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="is_direct_shipping" name="is_direct_shipping" value="1">
                                <label class="form-check-label" for="is_direct_shipping">
                                    Árazatlan szállító (közvetlen partneri szállítás)
                                </label>
                            </div>
                        </div>

                        {{-- Jobb oszlop --}}
                        <div class="col-md-6 d-flex flex-column justify-content-between">
                            
                            <div class="form-group mb-3">
                                <label for="note">Megjegyzés a rendeléshez (opcionális):</label>
                                <textarea name="note" id="note" class="form-control" rows="4" placeholder="Ha bármit szeretnél jelezni a rendeléshez...">{{ old('note') }}</textarea>
                            </div>

                            <div class="form-group d-flex align-items-start mb-3">
                                <input class="form-check-input me-2 mt-1" type="checkbox" value="" id="flexCheckDefault" required>
                                <label class="form-check-label" for="flexCheckDefault">
                                    Elolvastam és elfogadom az <a href="{{ route('aszf') }}">ÁSZF-et</a> és az <a href="{{ route('adatkezelesi-nyilatkozat') }}">Adatkezelési nyilatkozatot</a>.
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Véglegesítés gomb középen --}}
                    <div class="row">
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary bg-gradient mt-3">Véglegesítés</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
function updateShippingPreview(select) {
    const selected = select.options[select.selectedIndex];

    document.getElementById('preview-address').textContent = selected.dataset.address;
    document.getElementById('preview-receiver').textContent = selected.dataset.receiver;
    document.getElementById('preview-phone').textContent = selected.dataset.phone;
}

// Frissítjük elsőként betöltéskor is
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('shipping_address_id');
    if (select) updateShippingPreview(select);
});
</script>

@endsection