<nav class="navbar navbar-light sticky-top navbar-expand-lg bg-opacity-75 fixed-top lead text-uppercase">
    <div class="container-fluid px-3">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img class="logo img-fluid" src="{{ asset('img/logo.svg') }}" alt="ElectroBusiness">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Menü megnyitása">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav align-items-lg-center mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Főoldal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contact') }}">Kapcsolat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('orderpage') }}">Webshop</a>
                </li>
            </ul>

            <form class="site-search ms-lg-auto me-lg-3" action="{{ route('search') }}" method="get" role="search">
                <label class="visually-hidden" for="global-search">Termék keresése</label>
                <div class="site-search__control">
                    <input id="global-search" name="query" type="search" autocomplete="off"
                        placeholder="Terméknév vagy cikkszám..."
                        value="{{ request('query', request('search')) }}"
                        data-autocomplete-url="{{ route('search.autocomplete') }}"
                        aria-controls="global-search-suggestions" aria-expanded="false">
                    <button type="submit" aria-label="Keresés" title="Keresés">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </button>
                </div>
                <div id="global-search-suggestions" class="search-suggestions" role="listbox"></div>
            </form>

            <ul class="navbar-nav align-items-lg-center">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Partner belépés</a>
                    </li>
                @endguest

                @auth
                    <li class="nav-item" id="cart-icon-container"
                        data-count-url="{{ route('cart.count') }}"
                        data-preview-url="{{ route('cart.preview') }}"
                        data-update-url="{{ route('cart.update') }}"
                        data-remove-url="{{ route('cart.remove') }}"
                        style="position: relative; z-index: 1050;">
                        <a class="nav-link position-relative" href="{{ route('cart') }}">
                            Kosár
                            <span id="cart-count" class="cart-badge badge-custom">
                                {{ session('cart') ? collect(session('cart'))->sum('quantity') : 0 }}
                            </span>
                        </a>
                        <div id="cart-preview" class="dropdown-menu p-2" style="display: none;"></div>
                    </li>
                    <li class="nav-item dropdown" style="z-index: 1040;">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false" aria-label="Felhasználói menü">
                            <i class="bi bi-person-fill" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('account') }}">Fiók</a></li>
                            @if(auth()->user()->is_admin)
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('adminpanel') }}">Admin</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Kijelentkezés</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
