<section class="shop-v6-results" aria-label="Terméktalálatok">
    <header class="shop-v6-results__header">
        <strong>{{ $products->total() }} termék találat</strong>
        <form action="{{ route('orderpage') }}" method="get">
            @foreach(request()->except('sort', 'page') as $key => $value)
                @if(is_array($value))
                    @foreach($value as $nestedKey => $nestedValue)
                        @if(is_array($nestedValue))
                            @foreach($nestedValue as $item)<input type="hidden" name="{{ $key }}[{{ $nestedKey }}][]" value="{{ $item }}">@endforeach
                        @else<input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">@endif
                    @endforeach
                @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
            @endforeach
            <label class="sr-only" for="catalog-sort">Rendezés</label>
            <select id="catalog-sort" name="sort" onchange="this.form.submit()">
                <option value="">Legújabb elöl</option>
                <option value="a_to_z" @selected(request('sort') === 'a_to_z')>A-Z sorrend</option>
                <option value="z_to_a" @selected(request('sort') === 'z_to_a')>Z-A sorrend</option>
                <option value="low_high" @selected(request('sort') === 'low_high')>Ár szerint növekvő</option>
                <option value="high_low" @selected(request('sort') === 'high_low')>Ár szerint csökkenő</option>
            </select>
        </form>
    </header>

    @if($products->count())
        <div class="shop-v6-product-grid">
            @foreach($products as $product)
                @include('components.home-v6.catalog-card', ['product' => $product])
            @endforeach
        </div>
        <nav class="shop-v6-pagination" aria-label="Termékoldalak">{{ $products->appends(request()->query())->links() }}</nav>
    @else
        <div class="shop-v6-empty">
            <span class="shop-v6-empty__mark" aria-hidden="true">?</span>
            <h2>Nincs találat</h2>
            <p>Próbáljon másik kifejezést, kategóriát vagy szűrőt.</p>
            <a class="v6-button v6-button--outline" href="{{ route('orderpage') }}">Szűrők törlése</a>
        </div>
    @endif
</section>
