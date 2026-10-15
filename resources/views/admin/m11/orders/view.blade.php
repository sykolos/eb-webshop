@extends('layouts.v6.admin')

@section('title', 'Rendelés '.$order->displayNumber())
@section('heading', $order->displayNumber())
@section('intro', ($order->user?->user_invoice?->company_name ?: $order->name).' · '.optional($order->created_at)->format('Y. m. d. H:i'))

@section('content')
<div class="v6-section">
    <div class="v6-section__header"><div><span class="v6-eyebrow">Rendelési adatlap</span><h2>{{ $order->displayNumber() }}</h2><p>{{ $order->trashed() ? 'Ez a rendelés visszaállítható törlésként van elrejtve.' : 'A dokumentumok és árak a rendeléskori pillanatképet használják.' }}</p></div><div class="v6-actions"><a class="v6-button v6-button--outline" href="{{ session('orders_filter_url', route('adminpanel.orders')) }}">Vissza a listához</a><a class="v6-button v6-button--outline" href="{{ route('adminpanel.orders.getpdf', $order->id) }}">PDF letöltése</a></div></div>
    @if(session('success'))<div class="v6-toast" role="status">{{ session('success') }}</div>@endif

    <div class="v6-form-grid">
        <section class="v6-panel">
            <div class="v6-section__header"><div><h2>Feldolgozás</h2><p>Az állapotváltás ellenőrzött átmeneten keresztül történik.</p></div></div>
            @if(!$order->trashed())
                <form method="POST" action="{{ route('adminpanel.orders.status.update', $order->id) }}" class="v6-form">@csrf<label>Állapot<select name="status" required>@foreach($states as $state)<option value="{{ $state }}" @selected($order->status === $state)>{{ ucfirst($state) }}</option>@endforeach</select></label><label>Belső megjegyzés<textarea name="note" rows="3" maxlength="2000">{{ old('note', $order->status_note) }}</textarea></label><button class="v6-button v6-button--primary" type="submit">Módosítás mentése</button></form>
                <form method="POST" action="{{ route('adminpanel.orders.destroy', $order->id) }}" onsubmit="return confirm('A rendelés eltűnik az aktív listából, de az adatai megmaradnak. Folytatod?')" style="margin-top:18px">@csrf @method('DELETE')<button class="v6-button v6-button--danger" type="submit">Rendelés törlése</button></form>
            @else
                <span class="v6-status v6-status--danger">Törölt rendelés</span>
            @endif
            <p><small>Felelős: {{ $order->statusResponsible?->name ?: 'Nincs megadva' }} · Rögzítette: {{ $order->createdByAdmin?->name ?: 'Partneri rendelés' }}</small></p>
        </section>
        <section class="v6-panel"><div class="v6-section__header"><div><h2>Partner és szállítás</h2><p>{{ $order->email }}</p></div></div><p><strong>{{ $order->name }}</strong><br>{{ $order->user_invoice?->address ?: 'Számlázási cím nem elérhető' }}</p><hr><p><strong>{{ $order->shipping_receiver ?: ($order->user_shipping?->receiver ?: '-') }}</strong><br>{{ $order->zipcode }} {{ $order->city }}<br>{{ $order->address }}<br>{{ $order->phone ?: ($order->user_shipping?->phone ?: '-') }}</p><p>Fizetés: {{ $order->payment_method ? 'Készpénz' : 'Átutalás' }} · Közvetlen szállítás: {{ $order->is_direct_shipping ? 'Igen' : 'Nem' }}</p></section>
    </div>

    <section class="v6-panel" style="margin-top:18px"><div class="v6-section__header"><div><h2>Tételek és rögzített árak</h2><p>{{ $order->items->count() }} tétel</p></div></div><div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Termék</th><th>Mennyiség</th><th>Egységár</th><th>Nettó sorérték</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td><strong>{{ $item->product_title ?: ($item->product?->title ?: 'Történeti termék') }}</strong><br><small>{{ $item->serial_number ?: $item->product?->serial_number }}</small></td><td>{{ $item->quantity }} {{ $item->unit_measure ?: 'db' }}</td><td>{{ number_format((float) $item->unit_price, 0, ',', ' ') }} Ft</td><td>{{ number_format((float) ($item->line_net ?: ($item->unit_price * $item->quantity)), 0, ',', ' ') }} Ft</td></tr>@endforeach</tbody></table></div><div class="v6-actions" style="justify-content:flex-end;margin-top:16px"><strong>Nettó: {{ number_format((float) ($order->subtotal_net ?: $order->total), 0, ',', ' ') }} Ft</strong><strong>Bruttó: {{ number_format((float) $order->gross_total, 0, ',', ' ') }} Ft</strong></div>@if($order->note)<p style="white-space:pre-line"><strong>Rendelési megjegyzés:</strong><br>{{ $order->note }}</p>@endif</section>

    <section class="v6-panel" style="margin-top:18px"><div class="v6-section__header"><div><h2>Státuszelőzmény</h2><p>A változtatások adminnal, időponttal és megjegyzéssel naplózva.</p></div></div><div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Időpont</th><th>Állapot</th><th>Felelős</th><th>Megjegyzés</th></tr></thead><tbody>@forelse($order->statusHistories as $history)<tr><td>{{ optional($history->created_at)->format('Y. m. d. H:i') }}</td><td>{{ $history->from_status ? $history->from_status.' → ' : '' }}{{ $history->to_status }}</td><td>{{ $history->responsible?->name ?: '-' }}</td><td>{{ $history->note ?: '-' }}</td></tr>@empty<tr><td colspan="4">Nincs státuszelőzmény.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endsection
