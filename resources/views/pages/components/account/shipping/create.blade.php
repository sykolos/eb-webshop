@extends('pages.account')
@section('title','Új szállítási cím hozzáadása')
@section('content')
<div class="accounts-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-12">
                @include('pages.components.account.header')
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12">
                <section class="account-modify-box">
                    <p class="account-modify-box-title">Új szállítási cím</p>

                    <form action="{{ route('shipping.store') }}" method="POST">
                        @csrf
                        <div class="row mb-1">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="receiver">Átvevő</label>
                                    <input type="text" name="receiver" id="receiver"
                                        class="form-control @error('receiver') is-invalid @enderror"
                                        value="{{ old('receiver') }}" required>
                                    @error('receiver')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone">Átvevő telefonszáma</label>
                                    <input type="text" name="phone" id="phone" maxlength="12"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone') }}" required>
                                    @error('phone')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label for="zipcode">Irányítószám</label>
                                    <input type="text" name="zipcode" id="zipcode" maxlength="4"
                                        class="form-control @error('zipcode') is-invalid @enderror"
                                        value="{{ old('zipcode') }}" required>
                                    @error('zipcode')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="city">Város</label>
                                    <input type="text" name="city" id="city"
                                        class="form-control @error('city') is-invalid @enderror"
                                        value="{{ old('city') }}" required>
                                    @error('city')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group mb-3">
                                    <label for="address">Cím</label>
                                    <input type="text" name="address" id="address"
                                        class="form-control @error('address') is-invalid @enderror"
                                        value="{{ old('address') }}" required>
                                    @error('address')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="comment">Megjegyzés a futárnak</label>
                            <input type="text" name="comment" id="comment"
                                class="form-control @error('comment') is-invalid @enderror"
                                value="{{ old('comment') }}">
                            @error('comment')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">Mentés</button>
                            <a href="{{ route('account.modify') }}" class="btn btn-outline-secondary">Mégse</a>

                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
