<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .content {
            padding: 10px 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Köszönjük a rendelésedet!</h2>
        <img src="{{ $logoCid }}" alt="ElectroBusiness" style="max-height: 60px;">

        
    </div>

    <div class="content">
        <p>Kedves {{ $order->user->name ?? 'Vásárlónk' }},</p>

        <p>Örömmel értesítünk, hogy rendelésed sikeresen megérkezett hozzánk.</p>

        <p><strong>Rendelésszám:</strong> EBR-2025-{{ $order->id }}<br>
        <strong>Rendelés dátuma:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('Y.m.d') }}</p>

        <p>A rendelés részleteit a csatolt rendelés részletezőben találod.</p>

        <p>Kollégáink hamarosan feldolgozzák a rendelést, és a kiválasztott szállítási mód szerint kézbesítik azt számodra.</p>

        <p>Ha kérdésed van, fordulj hozzánk bizalommal:<br>
        <strong>Email:</strong> info@electrobusiness.hu<br>
        <strong>Telefon:</strong> +36 20 292 3769</p>

        <p>Köszönjük, hogy minket választottál!<br>
        Üdvözlettel:<br>
        <strong>ElectroBusiness Kft.</strong></p>
    </div>

    <div class="footer">
        Ez az üzenet automatikusan került kiküldésre. Kérjük, ne válaszolj rá közvetlenül.
    </div>
</body>
</html>
