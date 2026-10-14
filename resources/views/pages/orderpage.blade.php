@extends('layouts.master')
@section('title', $activeCategory ? $activeCategory->name : 'Webshop')

@section('content')
<main class="shop-page catalog-page">
    <section class="catalog-intro">
        <div class="container">
            <nav aria-label="Morzsamenü">
                <ol class="breadcrumb catalog-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Főoldal</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('orderpage') }}">Webshop</a></li>
                    @foreach($breadcrumbs as $breadcrumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" @if($loop->last) aria-current="page" @endif>
                            @if(!$loop->last)
                                <a href="{{ route('orderpage', ['category' => $breadcrumb->id]) }}">{{ $breadcrumb->name }}</a>
                            @else
                                {{ $breadcrumb->name }}
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
            <div class="catalog-heading d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <p class="eyebrow mb-2">ElectroBusiness katalógus</p>
                    <h1 class="mb-2">{{ $activeCategory?->name ?? (request('search') ? 'Keresési eredmények' : 'Termékek') }}</h1>
                    <p class="text-muted mb-0">Keressen cikkszám vagy terméknév alapján, és nézze meg az aktuális elérhetőséget.</p>
                </div>
                <a class="btn btn-outline-dark align-self-lg-end" href="{{ route('quickorder') }}">
                    <i class="bi bi-lightning-charge" aria-hidden="true"></i> Gyorsrendelés
                </a>
            </div>
        </div>
    </section>

    <div class="container py-4">
        <div class="catalog-view-switcher d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div class="small text-muted">{{ $products->total() }} termék a katalógusban</div>
            <form id="viewModeForm" method="get" action="{{ route('orderpage') }}" class="d-flex gap-2">
                @foreach(request()->except('view', 'page') as $key => $value)
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
                <button type="submit" name="view" value="normal" class="btn btn-sm {{ $viewMode === 'quick' ? 'btn-outline-dark' : 'btn-dark' }}">
                    <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i> Katalógus
                </button>
                <button type="submit" name="view" value="quick" class="btn btn-sm {{ $viewMode === 'quick' ? 'btn-dark' : 'btn-outline-dark' }}">
                    <i class="bi bi-lightning-charge" aria-hidden="true"></i> Gyorsnézet
                </button>
            </form>
        </div>

        @if($viewMode !== 'quick')
            <div class="row g-4 align-items-start">
                <aside class="col-lg-3 catalog-sidebar" aria-label="Katalógus szűrők">
                    <div class="sidebar-item card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Keresés és szűrés</span>
                            <i class="bi bi-sliders" aria-hidden="true"></i>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('orderpage') }}" method="get">
                                <label class="form-label" for="catalog-search">Keresés</label>
                                <input id="catalog-search" type="search" name="search" class="form-control mb-3" placeholder="Cikkszám vagy név" value="{{ request('search') }}">

                                @if($activeCategory)
                                    <input type="hidden" name="category" value="{{ $activeCategory->id }}">
                                @elseif(request('category') && in_array(request('category'), ['latest', 'highlighted'], true))
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                @endif

                                <label class="form-label" for="availability-filter">Elérhetőség</label>
                                <select id="availability-filter" name="availability" class="form-select mb-3">
                                    <option value="">Minden státusz</option>
                                    @foreach(\App\Models\Products::AVAILABILITY_STATUSES as $status => $label)
                                        <option value="{{ $status }}" @selected(request('availability') === $status)>{{ $label }}</option>
                                    @endforeach
                                </select>

                                @foreach($filterDefinitions as $definition)
                                    <fieldset class="mb-3">
                                        <legend class="form-label fs-6">{{ $definition->name }}</legend>
                                        @foreach($definition->values as $value)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="filter[{{ $definition->id }}][]" value="{{ $value->id }}"
                                                    id="filter-{{ $definition->id }}-{{ $value->id }}"
                                                    @checked(collect(data_get(request('filter', []), $definition->id, []))->contains((string) $value->id))>
                                                <label class="form-check-label" for="filter-{{ $definition->id }}-{{ $value->id }}">{{ $value->label }}</label>
                                            </div>
                                        @endforeach
                                    </fieldset>
                                @endforeach

                                @include('pages.components.shop.filters')
                                <button type="submit" class="btn btn-dark w-100 mt-2">
                                    <i class="bi bi-check2" aria-hidden="true"></i> Szűrés alkalmazása
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="sidebar-item card">
                        <div class="card-header">Kategóriák</div>
                        <div class="card-body">
                            <ul class="list-unstyled catalog-category-list mb-0">
                                <li><a href="{{ route('orderpage') }}" class="{{ !$activeCategory && !request('category') ? 'is-active' : '' }}">Összes termék</a></li>
                                <li><a href="{{ route('orderpage', ['category' => 'latest']) }}" class="{{ request('category') === 'latest' ? 'is-active' : '' }}">Új termékek</a></li>
                                <li><a href="{{ route('orderpage', ['category' => 'highlighted']) }}" class="{{ request('category') === 'highlighted' ? 'is-active' : '' }}">Kiemelt termékek</a></li>
                                @foreach($categories as $category)
                                    <li style="--category-level: {{ min((int) ($category->tree_level ?? 0), 5) }}">
                                        <a href="{{ route('orderpage', ['category' => $category->id]) }}" class="{{ $activeCategory?->id === $category->id ? 'is-active' : '' }}">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </aside>

                @include('pages.components.shop.standard')
            </div>
        @else
            @include('pages.components.shop.quick')
        @endif
    </div>
</main>
@endsection
