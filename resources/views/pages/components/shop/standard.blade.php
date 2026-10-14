<section class="col-lg-9 catalog-results" aria-label="Terméktalálatok">
    <header class="d-flex flex-column flex-sm-row align-items-sm-center border-bottom mb-4 pb-3 gap-3">
        <strong class="d-block">{{ $products->total() }} termék találat</strong>
        <form class="ms-sm-auto" action="{{ route('orderpage') }}" method="get">
            @foreach(request()->except('sort', 'page') as $key => $value)
                @if(is_array($value))
                    @foreach($value as $nestedKey => $nestedValue)
                        @if(is_array($nestedValue))
                            @foreach($nestedValue as $item)
                                <input type="hidden" name="{{ $key }}[{{ $nestedKey }}][]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">
                        @endif
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label class="visually-hidden" for="catalog-sort">Rendezés</label>
            <select id="catalog-sort" name="sort" onchange="this.form.submit()" class="form-select form-select-sm">
                <option value="">Legújabb elöl</option>
                <option value="a_to_z" @selected(request('sort') === 'a_to_z')>A-Z sorrend</option>
                <option value="z_to_a" @selected(request('sort') === 'z_to_a')>Z-A sorrend</option>
                <option value="low_high" @selected(request('sort') === 'low_high')>Ár szerint növekvő</option>
                <option value="high_low" @selected(request('sort') === 'high_low')>Ár szerint csökkenő</option>
            </select>
        </form>
    </header>

    @if($products->count())
        <div class="products-row">
            @foreach($products as $product)
                <x-product-box :product="$product" />
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @else
        <div class="catalog-empty text-center py-5 px-3">
            <i class="bi bi-search fs-1" aria-hidden="true"></i>
            <h2 class="h4 mt-3">Nincs találat</h2>
            <p class="text-muted">Próbáljon másik kifejezést, kategóriát vagy szűrőt.</p>
            <a class="btn btn-outline-dark" href="{{ route('orderpage') }}">Szűrők törlése</a>
        </div>
    @endif
</section>
