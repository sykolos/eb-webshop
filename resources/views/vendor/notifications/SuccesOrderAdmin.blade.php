<h2>Új rendelés érkezett a webshopon keresztül!</h2>

<p><strong>Rendelésszám:</strong> EBR-2025-{{ $order->id }}<br>
<strong>Rendelés dátuma:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('Y.m.d H:i') }}<br>
<strong>Közvetlen szállítás:</strong> {{ $order->is_direct_shipping ? 'Igen' : 'Nem' }}<br>
<strong>Fizetési mód:</strong> {{ $order->payment_method ? 'Készpénz' : 'Átutalás' }}</p>

<hr>

<h4>Vásárló adatai</h4>
<p>
    Név: {{ $order->user->name ?? '-' }}<br>
    E-mail: {{ $order->user->email ?? '-' }}<br>
    Telefonszám: {{ $order->user_shipping->phone ?? '-' }}
</p>

<h4>Szállítási cím</h4>
<p>
    {{ $order->user_shipping->receiver ?? '-' }}<br>
    {{ $order->user_shipping->zipcode }} {{ $order->user_shipping->city }}<br>
    {{ $order->user_shipping->address }}
</p>

<h4>Számlázási cím</h4>
<p>
    {{ $order->user_invoice->company_name ?? '-' }}<br>
    {{ $order->user_invoice->zipcode }} {{ $order->user_invoice->city }}<br>
    {{ $order->user_invoice->address }}
</p>

@if($order->note)
<h4>Megjegyzés:</h4>
<p style="white-space: pre-line;">{{ $order->note }}</p>
@endif

<p>A rendelés részleteit a csatolt rendelés részletezőben találod.</p>
