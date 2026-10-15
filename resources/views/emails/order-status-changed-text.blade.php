Rendelési állapot frissítve: {{ $order->displayNumber() }}

{{ $history->from_status ?: 'Új' }} -> {{ $history->to_status }}
@if($history->note)Megjegyzés: {{ $history->note }}@endif
