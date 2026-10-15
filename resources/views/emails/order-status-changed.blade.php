<!doctype html>
<html lang="hu">
<body style="font-family:Arial,sans-serif;color:#202529;line-height:1.5">
    <h2>Rendelési állapot frissítve</h2>
    <p>A(z) <strong>{{ $order->displayNumber() }}</strong> rendelés állapota megváltozott.</p>
    <p><strong>{{ $history->from_status ?: 'Új' }}</strong> → <strong>{{ $history->to_status }}</strong></p>
    @if($history->note)<p><strong>Megjegyzés:</strong><br>{{ $history->note }}</p>@endif
    <p>A rendelés részleteit a partnerfiókban találod.</p>
</body>
</html>
