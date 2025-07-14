@extends('pages.account')
@section('title','Felhasználói adatok')
@section('content')
<div class="accounts-page bg-light py-4"> 
    <div class="container-fluid">
        <div class="row justify-content-center">
            {{-- Bal oldali menü --}}
            <div class="col-lg-3 col-md-4 mb-4">
                @include('pages.components.account.header')
            </div>

            {{-- Jobb oldali fő doboz --}}
            <div class="col-lg-9 col-md-8">
                <div class="bg-white rounded shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
                        <h2 class="section-header m-0">Felhasználó adatok</h2>                        
                    </div>

                    <div class="row gy-4">
                        {{-- Számlázási adatok --}}
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="text-muted mb-3">📄 Számlázási adatok</h6>
                                <div class="border rounded p-3 mb-3 bg-white position-relative">
                                    <p><strong>Cégnév:</strong> {{ auth()->user()->user_invoice->company_name ?? '-' }}</p>
                                    <p><strong>Ország:</strong> {{ auth()->user()->user_invoice->country ?? '-' }}</p>
                                    <p><strong>Megye:</strong> {{ auth()->user()->user_invoice->state ?? '-' }}</p>
                                    <p><strong>Cím:</strong>
                                        <address class="mb-0">
                                            {{ auth()->user()->user_invoice->zipcode ?? '-' }},
                                            {{ auth()->user()->user_invoice->city ?? '-' }},
                                            {{ auth()->user()->user_invoice->address ?? '-' }}
                                        </address>
                                    </p>
                                    <p><strong>Adószám:</strong> {{ auth()->user()->user_invoice->vatnumber ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Kiszállítási adatok --}}
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="text-muted mb-3">🚚 Kiszállítási címek</h6>

                            {{-- Select mező --}}
                            <div class="mb-3">
                                <label for="shipping-select" class="form-label">Válassz szállítási címet</label>
                                <select id="shipping-select" class="form-select">
                                    @foreach(auth()->user()->user_shipping ?? [] as $index => $shipping)
                                        <option 
                                            value="shipping-{{ $shipping->id }}" 
                                            @if($loop->first) selected @endif
                                        >
                                            {{ $shipping->zipcode }}, {{ $shipping->city }} {{ $shipping->address }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kártyák --}}
                            @forelse(auth()->user()->user_shipping ?? collect() as $shipping)
                                <div 
                                    class="shipping-card border rounded p-3 mb-3 bg-white position-relative" 
                                    id="shipping-{{ $shipping->id }}" style="{{ $loop->first ? '' : 'display: none;' }}">
                                    

                                    <p><strong>Átvevő neve:</strong> {{ $shipping->receiver }}</p>
                                    <p><strong>Telefonszám:</strong> {{ $shipping->phone }}</p>
                                    <p><strong>Cím:</strong>
                                        <address class="mb-0">
                                            {{ $shipping->zipcode }},
                                            {{ $shipping->city }},
                                            {{ $shipping->address }}
                                        </address>
                                    </p>
                                    @if($shipping->comment)
                                        <p><strong>Megjegyzés:</strong> {{ $shipping->comment }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted">Nincs megadott szállítási cím.</p>
                            @endforelse
                        </div>
                    </div>
                    </div>
                    <div class="text-center mt-4"> 
                        <a class="btn btn-primary btn-lg px-4 me-sm-3 bg-gradient" href="{{ route('account.modify') }}">Adatok szerkesztése</a>
                    </div>
                </div> {{-- fehér doboz vége --}}
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('shipping-select');
        const cards = document.querySelectorAll('.shipping-card');

        select.addEventListener('change', function () {
            const selectedId = this.value;
            cards.forEach(card => {
                if (card.id === selectedId) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>



@endsection