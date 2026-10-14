@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Admin kétlépcsős belépés</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if ($challenge)
        <p>Írd be a hitelesítő alkalmazás aktuális kódját vagy egy megmaradt helyreállító kódot.</p>
        <form method="post" action="{{ route('admin.2fa.verify') }}" class="d-flex gap-2">
            @csrf
            <input name="code" class="form-control" autocomplete="one-time-code" inputmode="numeric" required>
            <button class="btn btn-primary">Ellenőrzés</button>
        </form>
    @elseif ($setup)
        <p>Fiók: <strong>{{ auth()->user()->email }}</strong></p>
        <p>Secret: <code>{{ $secret }}</code></p>
        <p>Ha nincs QR-olvasás, ezt az URI-t add meg kézzel:</p>
        <textarea class="form-control mb-3" rows="3" readonly>{{ $provisioningUri }}</textarea>
        <p>Helyreállító kódok, mentsd őket biztonságos helyre:</p>
        <pre class="border p-3">{{ implode(PHP_EOL, $recoveryCodes) }}</pre>
        <form method="post" action="{{ route('admin.2fa.confirm') }}" class="d-flex gap-2">
            @csrf
            <input name="code" class="form-control" autocomplete="one-time-code" inputmode="numeric" required>
            <button class="btn btn-primary">Bekapcsolás</button>
        </form>
    @endif
</div>
@endsection
