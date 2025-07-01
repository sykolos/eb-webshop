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
                <th>Szállító:</th>
                <th>Szállítási cím:</th>
            </thead>
            <tbody>
                <tr>
                    <td>ElectroBusiness Kft.</td>
                    <td>{{auth()->user()->user_invoice->company_name}}</td>
                </tr>
                <tr>
                    <td>Adószám: 26160874-2-13</td>
                    <td>Adószám: {{auth()->user()->user_invoice->vatnumber}}</td>
                </tr>
                <tr>
                    <td>Cím: 2600 Vác, Zrínyi Miklós utca 41/b</td>
                    <td>Cim: {{auth()->user()->user_shipping->zipcode}}&nbsp;{{auth()->user()->user_shipping->city}},&nbsp;{{auth()->user()->user_shipping->address}}</td>
                </tr>
                <tr>
                    <td>Elérhetoség: +36 20 292 3769</td>
                    <td>Elérhetoség: {{auth()->user()->user_shipping->phone}}</td>
                </tr>
            </tbody>
        </table>
        <table class="table">
            <tbody>
                <tr>
                    <td>Rendelesszam:EBR-2024-{{$order->id}}</td>
                    <td>Rednelesi datum:{{\Carbon\Carbon::parse($order->created_at)->format('d/m/Y')}}</td>
                    <td>Varhato kiszállítás:</td>
                </tr>
            </tbody>
        </table>
            
        
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
                        {{--@if(substr($item->product->special_prices->pluck( 'price' ), 2, -2)!="" && substr($item->product->special_prices->pluck( 'user_id' ), 2, -2)==auth()->user()->id)
                                  <p><span style="font-size:8pt;">  Kezdvezmény: {{round((int)substr($item->product->special_prices->pluck( 'price' ), 2, -2)/$item->product->price,2)}}%</p> 
                        @endif--}}
                                </td>                                
                                <td>{{$item->quantity}}</td>            
                                <td>{{$item->product->product_unit->measure}}</td>                    
                                <td>
                                    @if(substr($item->product->special_prices->pluck( 'price' ), 2, -2)!="" && substr($item->product->special_prices->pluck( 'user_id' ), 2, -2)==auth()->user()->id)
                                    {{substr($item->product->special_prices->pluck( 'price' ), 2, -2)}}
                                    @else
                                    {{$item->product->price}}
                                    @endif
                                </td>
                                <td>
                                    @if(substr($item->product->special_prices->pluck( 'price' ), 2, -2)!="" && substr($item->product->special_prices->pluck( 'user_id' ), 2, -2)==auth()->user()->id)
                                    {{(int)substr($item->product->special_prices->pluck( 'price' ), 2, -2)*$item->quantity}}
                                    @else
                                    {{$item->product->price*$item->quantity}}
                                    @endif
                                     Ft
                                </td>
                </tr>
                @endforeach
            </tbody>
        </table>
</main>
</body>
</html>


