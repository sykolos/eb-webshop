<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Szállítólevél</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
        .no-border td, .no-border th { border: none; }
        .section-title { background-color: #f2f2f2; font-weight: bold; padding: 6px; }
        .text-right { text-align: right; }
        .meta-table td { padding: 4px 8px; border: 1px solid #444; }
        .meta-table td:first-child { font-weight: bold; }

        .logo-table td {
            border: none;
            vertical-align: top;
        }

        .box-table td {
            vertical-align: top;
            padding: 10px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }
    </style>
</head>
<body>

<table class="logo-table">
    <tr>
        <td>
            <h1>Rendelés részletező</h1>
            <p><em>Ez a dokumentum nem minősül számlának</em></p>
        </td>
        <td style="text-align: right;">
            <img src="{{ public_path('img/logo.svg') }}" alt="Logo" style="height: 60px;">
        </td>
    </tr>
</table>

<table class="box-table" style="width: 100%; border: 1px solid #444;">
    <tr>
        <td style="width: 50%; border-right: 1px solid #444;">
            <strong>Szállító:</strong><br>
            ElectroBusiness Kft.<br>
            2600 Vác, Zrínyi Miklós utca 41/b<br>
            Tel: +36 20 292 3769
        </td>
        <td style="width: 50%;">
            <strong>Szállítási cím:</strong><br>
            {{ $order->user_shipping->receiver ?? '-' }}<br>
            {{ $order->user_shipping->zipcode }} {{ $order->user_shipping->city }} {{ $order->user_shipping->address }}<br>
            Tel: {{ $order->user_shipping->phone ?? '-' }}
        </td>
    </tr>
</table>

<table class="meta-table">
    <tr>
        <td>Rendelésszám:</td>
        <td>EBR-2024-{{ $order->id }}</td>
        <td>Rendelési dátum:</td>
        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y.m.d') }}</td>
    </tr>
    <tr>
        <td>Fizetési mód:</td>
        <td>{{ $order->payment_method ? 'Készpénz' : 'Átutalás' }}</td>
        <td>Közvetlen szállítás:</td>
        <td>{{ $order->is_direct_shipping ? 'Igen' : 'Nem' }}</td>
    </tr>
</table>

<div class="section-title">Termékek</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Termék neve</th>
            <th>Mennyiség</th>
            <th>Egység</th>
            <th>Egységár</th>
            <th>Összeg</th>
        </tr>
    </thead>
    <tbody>
        @php $netTotal = 0; @endphp
        @foreach ($order->items as $index => $item)
            @php
                $lineTotal = $item->unit_price * $item->quantity;
                $netTotal += $lineTotal;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->title }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->product->product_unit->measure }}</td>
                <td>{{ number_format($item->unit_price, 0, ',', ' ') }} Ft</td>
                <td>{{ number_format($lineTotal, 0, ',', ' ') }} Ft</td>
            </tr>
        @endforeach
    </tbody>
</table>

@php
    $vat = $netTotal * 0.27;
    $grossTotal = $netTotal + $vat;
@endphp

<table>
    <tbody>
        <tr>
            <td colspan="5" class="text-right"><strong>Nettó végösszeg:</strong></td>
            <td>{{ number_format($netTotal, 0, ',', ' ') }} Ft</td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><strong>Áfa tartalom (27%):</strong></td>
            <td>{{ number_format($vat, 0, ',', ' ') }} Ft</td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><strong>Bruttó végösszeg:</strong></td>
            <td><strong>{{ number_format($grossTotal, 0, ',', ' ') }} Ft</strong></td>
        </tr>
    </tbody>
</table>

</body>
</html>
