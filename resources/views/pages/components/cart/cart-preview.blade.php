@if(count($cart) > 0)
    <ul class="list-group list-group-flush">
        @foreach($cart as $item)
            @php
                $product = $item['product'];
                $quantity = (int) $item['quantity'];
                $unit = $item['m'] ?? 'db';
                $price = floatval($product['price'] ?? 0);
                $lineTotal = $price * $quantity;
                $image = $product['image'] ?? null;
            @endphp
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    @if($image)
                        <img src="{{ asset('storage/' . $image) }}" alt="Termék" width="50" height="50" class="rounded border">
                    @else
                        <div class="bg-light rounded" style="width: 50px; height: 50px;"></div>
                    @endif
                    <div>
                        <strong class="d-block text-truncate" style="max-width: 200px;">
                            {{ $product['title'] ?? 'Névtelen termék' }}
                        </strong>
                        <small>{{ $quantity }} {{ $unit }} × {{ number_format($price, 0, ',', ' ') }} Ft</small>
                    </div>
                    <div class="input-group input-group-sm mt-1" style="max-width: 120px;">
                        <button class="btn btn-outline-secondary btn-decrease" data-key="{{ $loop->index }}">−</button>
                        <input type="text" readonly class="form-control text-center quantity-display" value="{{ $quantity }}">
                        <button class="btn btn-outline-secondary btn-increase" data-key="{{ $loop->index }}">+</button>
                    </div>

                </div>
                <div>
                    <button class="btn btn-sm btn-outline-danger btn-remove ms-2" data-key="{{ $loop->index }}">×</button>

                    <strong>{{ number_format($lineTotal, 0, ',', ' ') }} Ft</strong>
                    
                </div>
            </li>
        @endforeach
        <li class="list-group-item d-flex justify-content-between border-top pt-2">
            <strong>Összesen (nettó):</strong>
            <strong>{{ number_format($totalNet, 0, ',', ' ') }} Ft</strong>
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
