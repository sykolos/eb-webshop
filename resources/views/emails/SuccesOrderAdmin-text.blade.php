Új rendelés érkezett.

Rendelésszám: {{ $order->displayNumber() }}
Vásárló: {{ $order->user->name ?? '-' }}
Email: {{ $order->user->email ?? '-' }}

A részletek a csatolt PDF-ben találhatók.
