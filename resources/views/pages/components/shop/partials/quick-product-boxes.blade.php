@if($products->isEmpty())
    <div class="alert alert-warning text-center w-100">Nincs a keresésnek megfelelő termék.</div>
@else
    @foreach($products as $product)
        <div class="mb-3">
            @include('pages.components.shop.partials.quick-product-box', ['product' => $product])
        </div>
    @endforeach

    <div class="d-flex justify-content-center mt-4">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
@endif