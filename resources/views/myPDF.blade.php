<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Document</title>
    <style>
        
    </style>
</head>
<body>
    <header class="text-center">
    <h1>Rendelés részletei</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Szállító:</th>
                <th>Szállítási cím:</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ElectroBusiness Kft.</td>
                <td>{{ $order->user_invoice->company_name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Cím: 2600 Vác, Zrínyi Miklós utca 41/b</td>
                <td>Átvevő neve: {{ $order->user_shipping->receiver ?? '-' }}</td>
            </tr>
            <tr>
                <td>Elérhetőség: +36 20 292 3769</td>
                <td>
                    Cím: {{ $order->user_shipping->zipcode }} 
                    {{ $order->user_shipping->city }}, 
                    {{ $order->user_shipping->address }}
                </td>
            </tr>
            <tr>
                
                <td>Elérhetőség: {{ $order->user_shipping->phone }}</td>
            </tr>
            <tr>
                <td></td>

            </tr>
        </tbody>
    </table>

    <table class="table">
        <tbody>
            <tr>
                <td>Rendelésszám: EBR-2024-{{ $order->id }}</td>
                <td>Rendelési dátum: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
                {{-- <td>Várható kiszállítás:</td> --}}
            </tr>
            <tr>
                <td>Fizetési mód: {{ $order->payment_method ? 'Készpénz' : 'Átutalás' }}</td>
                <td>Közvetlen szállítás: {{ $order->is_direct_shipping ? 'Igen (árazatlan szállító)' : 'Nem' }}</td>
            </tr>
        </tbody>
    </table>

    @if($order->note)
    <hr>
    <h4>Vásárlói megjegyzés</h4>
    <p>{!! nl2br(e($order->note)) !!}</p>
    @endif
</header>

    <main>
        <h2 class="text-center"> Rendelt termékek</h2>
        <table class="table tablestripped">
            <thead>
                <th>Termék neve</th>
                <th>Mennyiség</th>
                <th>Menny.egység</th>
                <th>Egység ár</th>
                <th>Ár</th>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                <tr>
                    <td>{{$item->product->title}} 
                        
                                </td>                                
                                <td>{{$item->quantity}}</td>            
                                <td>{{$item->product->product_unit->measure}}</td>                    
                                
                                <td>{{ $item->unit_price }} Ft</td>
                                <td>{{ $item->unit_price * $item->quantity }} Ft</td>
                </tr>
                @endforeach
            </tbody>
        </table>
</main>
</body>
</html>


