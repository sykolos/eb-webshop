@if(count($cart) > 0)
    <ul class="list-group list-group-flush">
        @foreach($cart as $item)
            @php
                $product = $item['product'];
                $quantity = (int) $item['quantity'];
                $unitQty = (int) ($item['q'] ?? 1); // pl. 16
                $unit = $item['m'] ?? 'db';
                $image = $product['image'] ?? null;

                // Teljes összeg: unitprice * q
                $lineTotal = App\Models\Cart::unitprice($item) * $unitQty;

                // Egy darab (csomag) ára:
                $pricePerPiece = $quantity > 0
                    ? $lineTotal / $quantity
                    : 0;
            @endphp

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    @if($image)
                        <img src="{{ asset('storage/' . $image) }}" alt="Termék" width="50" height="50" class="rounded border">
                    @else
                        <div class="bg-light rounded" style="width: 50px; height: 50px;"></div>
                    @endif

                    <div class="flex-grow-1">
                        <strong class="d-block text-truncate" style="max-width: 200px;">
                            {{ $product['title'] ?? 'Névtelen termék' }}
                        </strong>
                        <small>
                            {{ $quantity }} db × {{ $unitQty }}{{ $unit }} = {{ $quantity * $unitQty }}{{ $unit }}
                        </small>
                    </div>

                    <div class="input-group input-group-sm mt-1" style="max-width: 120px;">
                        <button class="btn btn-decrease" data-key="{{ $loop->index }}">−</button>
                        <input type="text" readonly class="form-control text-center quantity-display" value="{{ $quantity }}">
                        <button class="btn btn-increase" data-key="{{ $loop->index }}">+</button>
                        <button class="btn btn-sm  btn-remove ms-2" data-key="{{ $loop->index }}">×</button>
                    </div>
                </div>

                <div class="text-end">
                    
                    <strong class="d-block mt-1">
                        {{ number_format($lineTotal, 0, ',', ' ') }} Ft
                    </strong>
                </div>
            </li>
        @endforeach

        <li class="list-group-item d-flex justify-content-between border-top pt-2">
            <strong>Összesen (nettó):</strong>
            <strong>{{ number_format(App\Models\Cart::totalamount(), 0, ',', ' ') }} Ft</strong>
        </li>
    </ul>

    <div class="p-2 border-top">
        <a href="{{ route('cart') }}" class="btn btn-primary w-100">
            Irány a kosárhoz
        </a>
    </div>
@else
    <p class="text-muted px-3">A kosár üres.</p>
@endif
