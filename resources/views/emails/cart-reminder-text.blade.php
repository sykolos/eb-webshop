A kosaradban még vannak termékek

Kedves {{ $user->name ?: 'Partner' }},

A kosaradban {{ $cart->items->sum('quantity') }} termék várakozik. A kosarat itt nyithatod meg: {{ route('cart') }}
