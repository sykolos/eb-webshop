@extends('layouts.master')

@section('content')
<div class="container info-page">
    <div class="row justify-content-center pt-5">
      

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-dark text-white">{{ __('Elfelejtett jelszó') }}</div>

                <div class="card-body">
                    {{ __('Add meg az email címed!.') }}

                    <form method="POST" action="{{route('password.email')}}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" required autocomplete="current-email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Küldés') }}
                                </button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
