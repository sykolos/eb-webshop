@extends('layouts.master')
@section('title','Rendelés oldal')
@section('content')
<main class="shop-page">
    <section class="products">
        <div class="container w-100">
            <div class="row mt-5 product-list w-100">
                <div class="col-lg-3 ">
                    <div class="sidebar-item card sidebar-search-card my-3">
                        <h4 class="card-header bg-dark text-white">Keresés</h4>
                        <div>
                            <form action="{{ route('orderpage') }}" method="get" class="px-3 pt-2">
                                <input type="text" name="search" class="form-control mb-2" placeholder="Keresés..." value="{{ request('search') }}">

                                

                                @include('pages.components.shop.filters')

                                @if(request('category'))
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                @endif

                                <button type="submit" class="btn btn-dark w-100 mt-3">Szűrés</button>
                            </form>
                        </div>
                    </div>

                    <div class="sidebar-item card sidebar-category-card my-3">
                        <h4 class="card-header bg-dark text-white">Kategóriák</h4>
                        <div class="card-body mx-3">
                            <ul class="list-unstyled">
                                <li><a href="{{ route('orderpage') }}" class="text-dark">Összes Termék</a></li>
                                @foreach($categories as $category)
                                    <li><a href="{{ route('orderpage', ['category' => $category->id]) }}" class="text-dark">{{ $category->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <header class="d-sm-flex align-items-center border-bottom mb-4 pb-3">
                        <strong class="d-block py-2">Összesen {{ $products->total() }} termék találat</strong>
                        <div class="ms-auto">
                            <form action="{{ route('orderpage') }}" method="get">
                                @csrf
                                @foreach(request()->except('sort', 'page') as $key => $val)
                                    @if(is_array($val))
                                        @foreach($val as $v)
                                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                    @endif
                                @endforeach
                                <select name="sort" onchange="this.form.submit()" class="form-select d-inline-block w-auto border pt-1">
                                    <option value="">Alapértelmezett</option>
                                    <option value="a_to_z" {{ request('sort') == 'a_to_z' ? 'selected' : '' }}>A-Z sorrend</option>
                                    <option value="z_to_a" {{ request('sort') == 'z_to_a' ? 'selected' : '' }}>Z-A sorrend</option>
                                    <option value="low_high" {{ request('sort') == 'low_high' ? 'selected' : '' }}>Ár szerint növekő</option>
                                    <option value="high_low" {{ request('sort') == 'high_low' ? 'selected' : '' }}>Ár szerint csökkenő</option>
                                </select>
                            </form>
                        </div>
                    </header>

                    <div class="products-row">
                        @foreach($products as $product)
                            <x-product-box :product="$product" />
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@push('scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slider = document.getElementById('price-slider');
    const minInput = document.getElementById('min_price');
    const maxInput = document.getElementById('max_price');

    if (slider && minInput && maxInput) {
        const startMin = parseInt(minInput.value) || 0;
        const startMax = parseInt(maxInput.value) || 100000;

        noUiSlider.create(slider, {
            start: [startMin, startMax],
            connect: true,
            range: {
                min: 0,
                max: 100000
            },
            step: 100
        });

        slider.noUiSlider.on('update', function (values, handle) {
            minInput.value = Math.round(values[0]);
            maxInput.value = Math.round(values[1]);
        });
    }
});
</script>
@endpush

@endsection



