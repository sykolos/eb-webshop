@extends('layouts.public-v6')
@section('title', $activeCategory ? $activeCategory->name : 'Webshop')

@section('content')
<section class="shop-v6-page">
    <header class="shop-v6-intro">
        <div class="v6-container">
            <nav class="shop-v6-breadcrumbs" aria-label="Morzsamenü">
                <a href="{{ route('home') }}">Főoldal</a><span aria-hidden="true">/</span>
                <a href="{{ route('orderpage') }}">Webshop</a>
                @foreach($breadcrumbs as $breadcrumb)
                    <span aria-hidden="true">/</span>
                    @if(!$loop->last)
                        <a href="{{ route('orderpage', ['category' => $breadcrumb->id]) }}">{{ $breadcrumb->name }}</a>
                    @else
                        <span aria-current="page">{{ $breadcrumb->name }}</span>
                    @endif
                @endforeach
            </nav>
            <div class="shop-v6-intro__row">
                <div>
                    <p class="v6-eyebrow">ElectroBusiness katalógus</p>
                    <h1>{{ $activeCategory?->name ?? (request('search') ? 'Keresési eredmények' : 'Termékek') }}</h1>
                    <p>Keressen cikkszám vagy terméknév alapján, és lássa egy helyen a partnerárat, a kiszerelést és az aktuális elérhetőséget.</p>
                </div>
                <a class="v6-button v6-button--outline" href="{{ route('quickorder') }}">Gyorsrendelés</a>
            </div>
        </div>
    </header>

    <div class="v6-container shop-v6-content">
        <div class="shop-v6-toolbar">
            <div><strong>{{ $products->total() }} termék</strong>@if(request('search'))<span> a „{{ request('search') }}” keresésre</span>@endif</div>
            <form method="get" action="{{ route('orderpage') }}" class="shop-v6-view-form">
                @foreach(request()->except('view', 'page') as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $nestedKey => $nestedValue)
                            @if(is_array($nestedValue))
                                @foreach($nestedValue as $item)<input type="hidden" name="{{ $key }}[{{ $nestedKey }}][]" value="{{ $item }}">@endforeach
                            @else<input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">@endif
                        @endforeach
                    @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                @endforeach
                <button type="submit" name="view" value="normal" class="{{ $viewMode === 'quick' ? '' : 'is-active' }}">Katalógus</button>
                <button type="submit" name="view" value="quick" class="{{ $viewMode === 'quick' ? 'is-active' : '' }}">Gyorsnézet</button>
            </form>
        </div>

        @if($viewMode !== 'quick')
            <div class="shop-v6-layout">
                <aside class="shop-v6-sidebar" aria-label="Katalógus szűrők">
                    <details class="shop-v6-filter-panel" open>
                        <summary>Keresés és szűrés</summary>
                        <form action="{{ route('orderpage') }}" method="get" class="shop-v6-filter-form">
                            <label for="catalog-search">Keresés</label>
                            <input id="catalog-search" type="search" name="search" placeholder="Cikkszám vagy név" value="{{ request('search') }}">
                            @if($activeCategory)
                                <input type="hidden" name="category" value="{{ $activeCategory->id }}">
                            @elseif(request('category') && in_array(request('category'), ['latest', 'highlighted'], true))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            <label for="availability-filter">Elérhetőség</label>
                            <select id="availability-filter" name="availability">
                                <option value="">Minden státusz</option>
                                @foreach(\App\Models\Products::AVAILABILITY_STATUSES as $status => $label)
                                    <option value="{{ $status }}" @selected(request('availability') === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @foreach($filterDefinitions as $definition)
                                <fieldset>
                                    <legend>{{ $definition->name }}</legend>
                                    @foreach($definition->values as $value)
                                        <label class="shop-v6-check"><input type="checkbox" name="filter[{{ $definition->id }}][]" value="{{ $value->id }}" @checked(collect(data_get(request('filter', []), $definition->id, []))->contains((string) $value->id))><span>{{ $value->label }}</span></label>
                                    @endforeach
                                </fieldset>
                            @endforeach
                            @include('pages.components.shop.filters')
                            <button type="submit" class="v6-button v6-button--dark shop-v6-filter-submit">Szűrés alkalmazása</button>
                        </form>
                    </details>
                    <section class="shop-v6-category-panel">
                        <h2>Kategóriák</h2>
                        <nav aria-label="Termékkategóriák">
                            <a href="{{ route('orderpage') }}" class="{{ !$activeCategory && !request('category') ? 'is-active' : '' }}">Összes termék</a>
                            <a href="{{ route('orderpage', ['category' => 'latest']) }}" class="{{ request('category') === 'latest' ? 'is-active' : '' }}">Új termékek</a>
                            <a href="{{ route('orderpage', ['category' => 'highlighted']) }}" class="{{ request('category') === 'highlighted' ? 'is-active' : '' }}">Kiemelt termékek</a>
                            @foreach($categories as $category)
                                <a href="{{ route('orderpage', ['category' => $category->id]) }}" style="--category-level: {{ min((int) ($category->tree_level ?? 0), 5) }}" class="{{ $activeCategory?->id === $category->id ? 'is-active' : '' }}">{{ $category->name }}</a>
                            @endforeach
                        </nav>
                    </section>
                </aside>
                @include('pages.components.shop.standard')
            </div>
        @else
            <div class="shop-v6-quick">@include('pages.components.shop.quick')</div>
        @endif
    </div>
</section>
@endsection
