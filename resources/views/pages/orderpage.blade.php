@extends('layouts.master')
@section('title','Rendelés oldal')
@section('content')
<main class="shop-page">
    <section class="products">
        <div class="mb-4 text-center">
            <form id="viewModeForm" method="POST" action="{{ route('shop.setViewMode') }}"
                class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2">
                @csrf
                <input type="hidden" name="view" id="viewModeInput" value="">

                <button type="button"
                        class="btn {{ session('shop_view') === 'quick' ? 'btn-outline-dark' : 'btn-dark text-white' }}"
                        onclick="setViewMode('normal')">
                    📋 Normál nézet
                </button>

                <button type="button"
                        class="btn {{ session('shop_view') === 'quick' ? 'btn-dark text-white' : 'btn-outline-dark' }}"
                        onclick="setViewMode('quick')">
                    ⚡ Gyors nézet
                </button>
            </form>
        </div>
        <div class="container w-100 justify-content-center">
            

            <script>
                function setViewMode(mode) {
                    $('#viewModeInput').val(mode);
                    $('#viewModeForm').submit();
                }
            </script>

            <div class="row mt-5 product-list w-100">
                @if(session('shop_view') !== 'quick')
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

                                <button type="submit" class="btn btn-primary bg-gradient w-100 my-3">Keresés</button>
                            </form>
                        </div>
                    </div>

                    <div class="sidebar-item card sidebar-category-card my-3">
                        <h4 class="card-header bg-dark text-white">Kategóriák</h4>
                        <div class="card-body mx-3">
                            <ul class="list-unstyled">
                                <li><a href="{{ route('orderpage') }}" class="text-dark">Összes Termék</a></li>
                                {{-- Új termékek link --}}
                                <li>
                                    <a href="{{ route('orderpage', ['category' => 'latest']) }}"
                                    class="text-dark {{ request('category') === 'latest' ? 'text-danger' : '' }}">
                                        🆕 Új termékeink
                                    </a>
                                </li>
                                {{-- Kiemelt termékek link --}}
                                <li>
                                    <a href="{{ route('orderpage', ['category' => 'highlighted']) }}"
                                    class="text-dark {{ request('category') === 'highlighted' ? 'text-danger' : '' }}">
                                        ⭐ Kiemelt termékek
                                    </a>
                                </li>

                                {{-- Normál kategóriák --}}
                                @foreach($categories as $category)
                                    <li><a href="{{ route('orderpage', ['category' => $category->id]) }}"
                                        class="text-dark {{ request('category') == $category->id ? 'fw-bold text-danger' : '' }}">
                                        {{ $category->name }}
                                    </a></li>
                                @endforeach
                            </ul>

                        </div>
                    </div>
                </div>
                @endif
                
                        @if(session('shop_view') === 'quick')
                            @include('pages.components.shop.quick')
                        @else
                            @include('pages.components.shop.standard')
                        @endif
                
            </div>
        </div>
    </section>
</main>

@endsection



