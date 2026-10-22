@extends('layouts.public-v6')
@section('title', 'Kosár')

@section('content')
<section class="cart-v6-page">
    <div class="v6-container cart-v6-content">
        <nav class="shop-v6-breadcrumbs" aria-label="Morzsamenü"><a href="{{ route('home') }}">Főoldal</a><span aria-hidden="true">/</span><span aria-current="page">Kosár</span></nav>
        <div class="home-v6-section-heading"><div><p class="v6-eyebrow">Partnerfiók</p><h1>Kosár</h1><p>Tekintse át a csomagokat, módosítsa a mennyiséget, majd folytassa a rendelés véglegesítésével.</p></div></div>

        @if (session('success') || session('error'))<div class="shop-v6-alert {{ session('error') ? 'shop-v6-alert--warning' : '' }}" role="status">{{ session('success') ?: session('error') }}</div>@endif

        @if($lines)
            <div class="cart-v6-table-wrap">
                <table class="cart-v6-table">
                    <thead><tr><th>Termék</th><th>Csomagár</th><th>Mennyiség</th><th>Egység</th><th>Összesen</th><th><span class="sr-only">Művelet</span></th></tr></thead>
                    <tbody id="cart-body">
                        @forelse ($lines as $line)
                            @php $product = $line['product']; $quote = $line['quote']; $item = $line['item']; @endphp
                            <tr data-key="{{ $item->id }}" class="cart-row {{ $line['available'] ? '' : 'cart-row-unavailable' }}">
                                <td data-label="Termék">
                                    @if ($product)
                                        <a href="{{ route('product', $product->id) }}" class="cart-v6-product">
                                            <img src="{{ $product->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) : asset('img/landscape-placeholder.svg') }}" alt="{{ $product->title }}" width="72" height="54">
                                            <span><strong>{{ $product->title }}</strong><small>Cikkszám: {{ $product->serial_number }}</small></span>
                                        </a>
                                    @else<span>Ismeretlen termék</span>@endif
                                    @if (!$line['available'])<small class="cart-v6-warning">{{ $line['warning'] }}</small>@endif
                                </td>
                                <td data-label="Csomagár">{{ $quote ? number_format($quote['pack_net'], 0, ',', ' ') . ' Ft' : 'Nem elérhető' }}</td>
                                <td data-label="Mennyiség"><div class="cart-v6-quantity"><button class="quantity-decrease" data-key="{{ $item->id }}" data-change="-1" aria-label="Mennyiség csökkentése">−</button><span class="cart-quantity">{{ $line['quantity'] }}</span><button class="quantity-increase" data-key="{{ $item->id }}" data-change="1" aria-label="Mennyiség növelése">+</button></div></td>
                                <td data-label="Egység">{{ $line['pack_quantity'] }}{{ $line['measure'] }}</td>
                                <td data-label="Összesen" class="cart-subtotal">{{ $quote ? number_format($quote['line_net'], 0, ',', ' ') . ' Ft' : 'Nem elérhető' }}</td>
                                <td data-label="Törlés"><button class="cart-v6-remove cart-remove" data-key="{{ $item->id }}" aria-label="{{ $product?->title ?? 'Tétel' }} törlése">×</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="cart-v6-empty">A kosarad üres.</td></tr>
                        @endforelse
                        @if ($lines)<tr class="cart-total"><td colspan="6">Összesen nettó: <strong>{{ number_format($summary['net'], 0, ',', ' ') }} Ft</strong></td></tr>@endif
                    </tbody>
                </table>
            </div>
            <div id="ajax-spinner" class="cart-v6-spinner" hidden role="status">Kosár frissítése…</div>
            <div class="cart-v6-actions">
                @if (!$summary['has_unavailable'])<a href="{{ route('checkout') }}" class="v6-button v6-button--primary">Tovább a véglegesítéshez</a>@else<p class="cart-v6-warning">A rendelés folytatásához távolítsd el a már nem elérhető terméket.</p>@endif
            </div>
        @else
            <div class="shop-v6-empty"><span class="shop-v6-empty__mark" aria-hidden="true">0</span><h2>A kosarad üres</h2><p>Válassz termékeket a katalógusból, és azok itt jelennek meg.</p><a class="v6-button v6-button--dark" href="{{ route('orderpage') }}">Vissza a webshophoz</a></div>
        @endif
    </div>
</section>
@endsection
