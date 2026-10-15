<!-- Recommended Product section-->
<section class="recommended-products-section" id="recommended-products">
        @if($highlighted->count())
            @php
                $validHighlighted = $highlighted->filter(fn($item) => $item->product !== null);
            @endphp

            @if($validHighlighted->count())
            <div class="my-3 position-relative">
                <h3 class="mb-4 section-header">Kiemelt termékeink</h3>
                <div class="legacy-scroller highlighted-scroller">
                    <div class="legacy-scroller__track">
                        @foreach($validHighlighted as $item)
                            <div class="legacy-scroller__item">
                                <x-product-box :product="$item->product" />
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="text-center mt-4">
                <a href="{{ url('/shop?category=highlighted') }}" class="btn btn-primary btn-lg px-4 me-sm-3 bg-gradient">
                    Tovább a kiemelt termékekhez
                </a>
            </div>
            @endif
        @endif
        
    </section>  
    <!-- Category section-->
    <section class="category-section" id="top-categories">
        <div class="my-3 position-relative">
            <h3 class="mb-4 section-header">Legnépszerűbb kategóriáink</h3>
            <div class="legacy-scroller top-categories-scroller">
                @php
                    $validCategories = $topCategories->filter(fn($category) => isset($category->id) && isset($category->name));
                @endphp

                @if($validCategories->count())
                    <div class="legacy-scroller__track" id="top-categories-wrapper">
                        @foreach($validCategories as $category)
                            <div class="legacy-scroller__item">
                                <x-category-box :category="$category" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('orderpage') }}" class="btn btn-primary btn-lg px-4 me-sm-3 bg-gradient">
                Tovább a webshopra
            </a>
        </div>
    </section>
    <!-- Latest Products section-->
    <section class="latest-products-section pb-5" id="latest-products">
        <div class="my-3 position-relative">
            <h3 class="mb-4 section-header">Új termékeink</h3>
            <div class="legacy-scroller latest-products-scroller">
                @php
                    $validLatest = $latestProducts->filter(fn($product) => isset($product->id));
                @endphp

                @if($validLatest->count())
                    <div class="legacy-scroller__track" id="latest-products-wrapper">
                        @foreach($validLatest as $product)
                            <div class="legacy-scroller__item">
                                <x-product-box :product="$product" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="text-center mt-4">
                <a href="{{ route('orderpage', ['category' => 'latest']) }}" class="btn btn-primary btn-lg px-4 me-sm-3 bg-gradient">
                    Tovább az új termékekhez
                </a>
            </div>
    </section>
