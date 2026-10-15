@extends('layouts.master')
@section('title', 'Kosár')
@section('content')

<div class="row justify-content-center align-items-center page-header-title">
    <div class="col-md-6 text-center mb-5">
        <h2 class="heading-section section-header">Kosár</h2>
    </div>
</div>

@if (session('success') || session('error'))
    <div class="container mb-3">
        <div class="alert {{ session('error') ? 'alert-warning' : 'alert-success' }}">
            {{ session('success') ?: session('error') }}
        </div>
    </div>
@endif

<main class="cart-page">
    <div class="container">
        <div class="cart-table">
            <table class="table-responsive">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Termék</th>
                        <th>Csomagár</th>
                        <th>Csomag</th>
                        <th>Mennyiségi egység</th>
                        <th>Összesen</th>
                        <th>Törlés</th>
                    </tr>
                </thead>
                <tbody id="cart-body">
                    @forelse ($lines as $line)
                        @php
                            $product = $line['product'];
                            $quote = $line['quote'];
                            $item = $line['item'];
                        @endphp
                        <tr data-key="{{ $item->id }}" class="cart-row {{ $line['available'] ? '' : 'cart-row-unavailable' }}">
                            <td data-label="Termék" class="px-2">
                                @if ($product)
                                    <a href="{{ route('product', $product->id) }}" class="cart-item-title d-flex align-items-center gap-2">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" style="width:60px; height:auto">
                                        @endif
                                        <p class="m-0">{{ $product->title }}</p>
                                    </a>
                                @else
                                    <span>Ismeretlen termék</span>
                                @endif
                                @if (!$line['available'])
                                    <small class="d-block text-danger mt-1">{{ $line['warning'] }}</small>
                                @endif
                            </td>
                            <td data-label="Csomagár">{{ $quote ? number_format($quote['pack_net'], 0, ',', ' ') . ' Ft' : 'Nem elérhető' }}</td>
                            <td data-label="Csomag">
                                <div class="d-flex gap-2 align-items-center">
                                    <button class="btn btn-sm btn-outline-secondary quantity-decrease" data-key="{{ $item->id }}" data-change="-1" aria-label="Mennyiség csökkentése">−</button>
                                    <span class="cart-quantity">{{ $line['quantity'] }}</span>
                                    <button class="btn btn-sm btn-outline-secondary quantity-increase" data-key="{{ $item->id }}" data-change="1" aria-label="Mennyiség növelése">+</button>
                                </div>
                            </td>
                            <td data-label="Mennyiségi egység">{{ $line['pack_quantity'] }}{{ $line['measure'] }}</td>
                            <td data-label="Összesen" class="cart-subtotal" data-line-net="{{ $quote['line_net'] ?? '' }}">
                                {{ $quote ? number_format($quote['line_net'], 0, ',', ' ') . ' Ft' : 'Nem elérhető' }}
                            </td>
                            <td data-label="Törlés">
                                <button class="btn btn-danger btn-sm cart-remove" data-key="{{ $item->id }}" aria-label="Termék törlése">×</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-cart">A kosarad üres.</td></tr>
                    @endforelse
                    @if ($lines)
                        <tr class="cart-total">
                            <td colspan="6" class="text-center">
                                Összesen nettó: <strong>{{ number_format($summary['net'], 0, ',', ' ') }} Ft</strong>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div id="ajax-spinner" style="display:none; position: fixed; top: 30%; left: 50%; z-index: 9999; transform: translate(-50%, -50%);">
                <div class="spinner-border text-danger" role="status" style="width: 3rem; height: 3rem;"></div>
            </div>
        </div>
        <div class="cart-actions">
            @if ($lines && !$summary['has_unavailable'])
                <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg px-4 me-sm-3 bg-gradient">Tovább a véglegesítéshez</a>
            @elseif ($lines)
                <p class="text-danger mb-0">A rendelés folytatásához távolítsd el a már nem elérhető terméket.</p>
            @endif
        </div>
    </div>
</main>
@endsection
