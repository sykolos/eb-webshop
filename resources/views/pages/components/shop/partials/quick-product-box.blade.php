<div class="card p-3 quick-product-box">
    <div class="row align-items-center gx-3 gy-3 quick-box-body">
        {{-- Bal oldal: kép + infó --}}
        <div class="col-12 col-md d-flex align-items-start gap-3 quick-main">
            @php
                $imagePath = $product->image;
            @endphp

            {{-- Kép – csak md fölött --}}
            <div class="d-none d-sm-block flex-shrink-0" style="width: 64px;">
                <img src="{{ Storage::exists($imagePath) ? Storage::url($imagePath) : asset('img/landscape-placeholder.svg') }}"
                     class="img-fluid rounded border"
                     alt="{{ $product->title }}"
                     loading="lazy">
            </div>

            {{-- Szövegblokk --}}
            <div class="flex-grow-1" style="min-width: 0;">
                <h5 class="mb-1" style="white-space: normal;">
                    <a href="{{ route('product', $product->id) }}"
                       class="text-decoration-none text-dark"
                       style="display: inline-block; max-width: 100%; white-space: normal; word-break: break-word; overflow-wrap: break-word; hyphens: auto;"
                       lang="hu">
                        {{ str_replace(chr(160), ' ', $product->title) }}
                    </a>
                </h5>

                <div class="text-muted small mb-1" style="white-space: normal; word-break: break-word;">
                    {{ $product->description }}
                </div>

                @if($product->product_unit)
                    <div class="d-flex align-items-center gap-3 small flex-wrap">
                        <div>
                            <strong>Kiszerelés:</strong>
                            {{ $product->product_unit->unit }} ({{ $product->product_unit->quantity }}{{ $product->product_unit->measure }})
                        </div>
                        <div class="bg-light px-2 py-1 rounded border d-inline-flex align-items-center gap-1 text-danger fs-6 fw-bold unit-price-box">
                            <i class="bi bi-tag"></i>
                            Egységár: <strong class="text-dark">{{ $product->getPriceForUser() }} Ft</strong>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Jobb oldal: mennyiség + gomb --}}
        <div class="col-12 col-md-auto d-flex flex-column align-items-end align-items-md-center text-end quick-controls gap-2">
            @if($product->product_unit)
                <div class="d-flex flex-column align-items-end align-items-md-center">
                    <div class="input-group input-group-sm" style="max-width: 150px;">
                        <input type="number" name="quantity"
                               class="form-control quick-quantity"
                               min="1" value="1" required>
                        <span class="input-group-text">
                            x {{ $product->product_unit->quantity }}{{ $product->product_unit->measure }}
                        </span>
                    </div>
                    <div class="mt-1 text-center">
                        <span class="fw-bold text-danger calculated-price">0 Ft</span>
                        <span class="text-muted small">+ Áfa</span>
                    </div>
                </div>
            @endif

            <button class="btn btn-primary btn-lg w-100 w-md-auto bg-gradient add-to-cart"
                    data-id="{{ $product->id }}"
                    data-q="{{ $product->product_unit->quantity ?? 1 }}"
                    data-m="{{ $product->product_unit->measure ?? 'db' }}">
                Kosárba
            </button>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('.quick-quantity').each(function () {
        const $input = $(this);
        const container = $input.closest('.card');
        const priceText = container.find('.bg-light').text().replace(/[^0-9]/g, '');
        const price = parseFloat(priceText) || 0;
        const multiplier = {{ $product->product_unit->quantity ?? 1 }};
        const output = container.find('.calculated-price');

        const updatePrice = () => {
            const qty = parseInt($input.val()) || 0;
            const total = qty * multiplier * price;
            output.text(total.toLocaleString() + ' Ft');
        };

        updatePrice();
        $input.on('input', updatePrice);
    });

    
});
</script>
