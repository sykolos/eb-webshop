@if(count($lines) > 0)
    <div class="home-v6-cart-preview__heading"><strong>Kosár előnézete</strong><span>{{ count($lines) }} tétel</span></div>
    <ul class="home-v6-cart-preview__items">
        @foreach($lines as $line)
            @php
                $product = $line['product'];
                $quote = $line['quote'];
                $item = $line['item'];
            @endphp
            <li>
                <div class="home-v6-cart-preview__line">
                    <div>
                        <strong>
                            {{ $product?->title ?? 'Ismeretlen termék' }}
                        </strong>
                        <small>
                            {{ $line['quantity'] }} csomag × {{ $line['pack_quantity'] }}{{ $line['measure'] }}
                            @if (!$line['available'])
                                <span class="text-danger">· Nem rendelhető</span>
                            @endif
                        </small>
                    </div>
                    @if ($quote)<strong>{{ number_format($quote['line_net'], 0, ',', ' ') }} Ft</strong>@endif
                </div>
                <div class="home-v6-cart-preview__controls">
                    <button class="btn-decrease" type="button" data-key="{{ $item->id }}" aria-label="Mennyiség csökkentése">−</button>
                    <span>{{ $line['quantity'] }}</span>
                    <button class="btn-increase" type="button" data-key="{{ $item->id }}" aria-label="Mennyiség növelése">+</button>
                    <button class="btn-remove" type="button" data-key="{{ $item->id }}" aria-label="Termék törlése">Törlés</button>
                </div>
            </li>
        @endforeach
        <li class="home-v6-cart-preview__total">
            <strong>Összesen nettó</strong><strong>{{ number_format($summary['net'], 0, ',', ' ') }} Ft</strong>
        </li>
    </ul>
    <div class="home-v6-cart-preview__footer">
        <a href="{{ route('cart') }}" class="v6-button v6-button--primary v6-button--small">Irány a kosárhoz</a>
    </div>
@else
    <div class="home-v6-cart-preview__empty">A kosár üres.</div>
@endif
