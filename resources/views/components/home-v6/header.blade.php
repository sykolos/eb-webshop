<div class="home-v6-utility">
    <div class="v6-container home-v6-utility__inner">
        <span>Országos kiszállítás 24–48 órán belül</span>
        <div>
            <a href="{{ route('contact') }}">Kapcsolat</a>
            <a href="{{ route('gyik') }}">Segítség</a>
            <a href="{{ route('register') }}">Partnerprogram</a>
        </div>
    </div>
</div>
<header class="home-v6-header" data-home-header>
    <div class="v6-container home-v6-header__main">
        <button class="home-v6-menu-button" type="button" data-home-menu-toggle aria-expanded="false" aria-controls="home-v6-nav">Menü</button>
        <a class="home-v6-brand" href="{{ route('home') }}" aria-label="Electro Business főoldal">
            <img src="{{ asset('img/logo.svg') }}" alt="Electro Business">
        </a>
        <form class="home-v6-search" action="{{ route('search') }}" method="get" role="search" data-home-search-form>
            <label class="sr-only" for="home-v6-search-input">Termék keresése</label>
            <input id="home-v6-search-input" name="query" type="search" autocomplete="off" placeholder="Keress termékre vagy cikkszámra..." data-home-search-input data-autocomplete-url="{{ route('search.autocomplete') }}" aria-controls="home-v6-search-suggestions" aria-expanded="false">
            <button type="submit" aria-label="Keresés">Keresés</button>
            <div id="home-v6-search-suggestions" class="home-v6-search__suggestions" role="listbox"></div>
        </form>
        <div class="home-v6-actions">
            @auth
                <a href="{{ route('account') }}" class="home-v6-account-action"><small>Partnerfiók</small><strong>Fiókom</strong></a>
                <a id="cart-icon-container" href="{{ route('cart') }}" class="home-v6-cart-action"
                    data-count-url="{{ route('cart.count') }}"
                    data-update-url="{{ route('cart.update') }}"
                    data-remove-url="{{ route('cart.remove') }}">
                    <small>Kosár</small><strong><span data-home-cart-count>{{ auth()->user()->cart?->items()->sum('quantity') ?? 0 }}</span> tétel</strong>
                </a>
            @else
                <a href="{{ route('login') }}" class="home-v6-account-action"><small>Partnerfiók</small><strong>Belépés</strong></a>
                <a href="{{ route('register') }}" class="home-v6-cart-action"><small>Új partner</small><strong>Regisztráció</strong></a>
            @endauth
        </div>
    </div>
    <nav id="home-v6-nav" class="home-v6-nav" data-home-nav aria-label="Fő navigáció">
        <div class="v6-container home-v6-nav__inner">
            <a class="home-v6-nav__all" href="{{ route('orderpage') }}">Minden termék <span aria-hidden="true">⌄</span></a>
            @foreach(($headerCategories ?? collect())->take(5) as $category)
                <a href="{{ route('orderpage', ['category' => $category->id]) }}">{{ $category->name }}</a>
            @endforeach
            <a class="home-v6-nav__sale" href="{{ route('info.offers') }}">Akciók</a>
        </div>
    </nav>
</header>
