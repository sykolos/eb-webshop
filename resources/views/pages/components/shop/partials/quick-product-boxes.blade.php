@if($products->isEmpty())
    <div class="shop-v6-empty"><h2>Nincs találat</h2><p>A keresésnek megfelelő termék nem található.</p></div>
@else
    @foreach($products as $product)
        <div class="quick-list-item-wrap">
            @include('pages.components.shop.partials.quick-product-box', ['product' => $product])
        </div>
    @endforeach

    <nav class="shop-v6-pagination" aria-label="Lista nézet oldalai">{{ $products->appends(request()->query())->links() }}</nav>
@endif
