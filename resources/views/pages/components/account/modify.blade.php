

@extends('pages.account')
@section('title','Adatok szerkesztése')
@section('content')

<div class="accounts-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-12">
                @include('pages.components.account.header')
            </div>

            <div class="col-lg-9 col-md-8 col-sm-12">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="account-modify-box">
                    <p class="section-header">
                        Felhasználó adatok módosítása
                        @if($errors->any())
                            {!! implode('', $errors->all('<div>:message</div>')) !!}
                        @endif
                    </p>

                    <form action="{{ route('account.edit') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Felhasználói adatok --}}
                        <div class="user-information mb-4">
                            <h5 class="fw-bold mb-3">Felhasználói adatok:</h5>
                            <div class="row mb-1">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Felhasználó név</label>
                                        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ auth()->user()->name }}" required>
                                        @error('username')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="text" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ auth()->user()->email }}" required>
                                        @error('email')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <a href="{{ route('change-password') }}">Jelszó megváltoztatása</a>
                            </div>
                        </div>

                        {{-- Számlázási adatok --}}
                        <div class="invoice-information mb-4">
                            <h5 class="fw-bold mb-3">Számlázási adatok:</h5>
                            <div class="row mb-1">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Cégnév</label>
                                        <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ auth()->user()->user_invoice->company_name ?? '' }}" required>
                                        @error('company_name')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vatnumber" class="form-label">Adószám</label>
                                        <input type="text" name="vatnumber" id="vatnumber" minlength="13" maxlength="13" class="form-control @error('vatnumber') is-invalid @enderror" value="{{ auth()->user()->user_invoice->vatnumber ?? '' }}" required>
                                        @error('vatnumber')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="country" class="form-label">Ország</label>
                                        <select name="country" id="country" class="form-control">
                                            <option value="Magyarország" selected>Magyarország</option>
                                        </select>
                                        @error('country')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="zipcode" class="form-label">Irányítószám</label>
                                        <input type="text" name="zipcode" id="zipcode" maxlength="4" class="form-control @error('zipcode') is-invalid @enderror" value="{{ auth()->user()->user_invoice->zipcode ?? '' }}" required>
                                        @error('zipcode')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="state" class="form-label">Megye</label>
                                        <input type="text" name="state" id="state" class="form-control @error('state') is-invalid @enderror" value="{{ auth()->user()->user_invoice->state ?? '' }}" required>
                                        @error('state')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="city" class="form-label">Város</label>
                                        <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{ auth()->user()->user_invoice->city ?? '' }}" required>
                                        @error('city')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Cím</label>
                                        <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ auth()->user()->user_invoice->address ?? '' }}" required>
                                        @error('address')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kiszállítási címek --}}
                        <div class="shipping-information mb-4">
                            <h5 class="fw-bold mb-3">Kiszállítási címek:</h5>
                            <div class="mb-3">
                                <label for="shipping-select" class="form-label">Válassz szállítási címet</label>
                                <select id="shipping-select" class="form-select">
                                    @foreach(auth()->user()->user_shipping ?? [] as $shipping)
                                        <option value="shipping-{{ $shipping->id }}" @if ($loop->first) selected @endif>
                                            {{ $shipping->zipcode }}, {{ $shipping->city }} {{ $shipping->address }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @foreach(auth()->user()->user_shipping ?? [] as $shipping)
                                <div class="shipping-card card p-3 mb-3 border" id="shipping-{{ $shipping->id }}" style="{{ $loop->first ? '' : 'display:none;' }}">
                                    <p><strong>Átvevő:</strong> {{ $shipping->receiver }}</p>
                                    <p><strong>Telefon:</strong> {{ $shipping->phone }}</p>
                                    <p><strong>Cím:</strong> {{ $shipping->zipcode }} {{ $shipping->city }}, {{ $shipping->address }}</p>
                                    <p><strong>Megjegyzés:</strong> {{ $shipping->comment }}</p>

                                    <div class="d-flex gap-2 mt-2">
                                        <a href="{{ route('shipping.edit', ['address' => $shipping->id, 'selected' => $shipping->id]) }}" class="btn btn-sm btn-outline-primary">Szerkesztés</a>
                                        {{-- <form id="delete-form-{{ $shipping->id }}" action="{{ route('shipping.destroy', $shipping) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form> --}}
                                        <button type="button" onclick="if(confirm('Biztosan törlöd ezt a címet?')) document.getElementById('delete-form-{{ $shipping->id }}').submit();" class="btn btn-sm btn-outline-danger">Törlés</button>
                                    </div>
                                </div>
                            @endforeach

                            <a href="{{ route('shipping.create') }}" class="btn btn-success mt-3">+ Új cím hozzáadása</a>
                        </div>

                        <div class="form-group pt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="accept_terms" id="flexCheckDefault" required>
                                <label class="form-check-label" for="flexCheckDefault">
                                    Elolvastam és elfogadom az 
                                    <a href="{{ route('aszf') }}">ÁSZF-et</a> és az 
                                    <a href="{{ route('adatkezelesi-nyilatkozat') }}">Adatkezelési nyilatkozatot</a>.
                                </label>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button id="update_btn" type="submit" class="btn btn-primary bg-gradient mt-3">Módosítás</button>
                        </div>
                    </form>
                    {{-- Kívül a fő formon, hogy ne rontsa meg a DOM-ot --}}
                        @foreach(auth()->user()->user_shipping ?? [] as $shipping)
                            <form id="delete-form-{{ $shipping->id }}" action="{{ route('shipping.destroy', $shipping) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endforeach

                </section>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('shipping-select');
        const cards = document.querySelectorAll('.shipping-card');

        const urlParams = new URLSearchParams(window.location.search);
        const selectedId = urlParams.get('selected');
        if (selectedId) {
            select.value = 'shipping-' + selectedId;
        }

        function updateShippingCard(id) {
            cards.forEach(card => {
                card.style.display = (card.id === id) ? 'block' : 'none';
            });
        }

        updateShippingCard(select.value);

        select.addEventListener('change', function () {
            updateShippingCard(this.value);
        });
    });
</script>

@endsection
