@if(count($lines) > 0)
    <ul class="list-group list-group-flush">
        @foreach($lines as $line)
            @php
                $product = $line['product'];
                $quote = $line['quote'];
                $item = $line['item'];
            @endphp
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="flex-grow-1">
                        <strong class="d-block text-truncate" style="max-width: 220px;">
                            {{ $product?->title ?? 'Ismeretlen termék' }}
                        </strong>
                        <small>
                            {{ $line['quantity'] }} csomag × {{ $line['pack_quantity'] }}{{ $line['measure'] }}
                            @if (!$line['available'])
                                <span class="text-danger">· Nem rendelhető</span>
                            @endif
                        </small>
                    </div>
                    @if ($quote)
                        <strong>{{ number_format($quote['line_net'], 0, ',', ' ') }} Ft</strong>
                    @endif
                </div>
                <div class="input-group input-group-sm mt-2" style="max-width: 160px;">
                    <button class="btn btn-decrease" data-key="{{ $item->id }}" aria-label="Mennyiség csökkentése">−</button>
                    <input type="text" readonly class="form-control text-center quantity-display" value="{{ $line['quantity'] }}">
                    <button class="btn btn-increase" data-key="{{ $item->id }}" aria-label="Mennyiség növelése">+</button>
                    <button class="btn btn-sm btn-remove ms-2" data-key="{{ $item->id }}" aria-label="Termék törlése">×</button>
                </div>
            </li>
        @endforeach
        <li class="list-group-item d-flex justify-content-between border-top pt-2">
            <strong>Összesen nettó:</strong>
            <strong>{{ number_format($summary['net'], 0, ',', ' ') }} Ft</strong>
        </li>
    </ul>
    <div class="p-2 border-top text-center">
        <a href="{{ route('cart') }}" class="btn btn-primary btn-lg px-4 bg-gradient text-white">Irány a kosárhoz</a>
    </div>
@else
    <p class="text-muted px-3">A kosár üres.</p>
@endif
