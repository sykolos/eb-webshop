<header class="v6-site-header" x-data="{ open: false }">
    <div class="v6-container v6-header-row">
        <a class="v6-brand" href="{{ route('home') }}" aria-label="Electro Business főoldal">
            <img src="{{ asset('img/logo.svg') }}" alt="">
            <span>Electro Business</span>
        </a>

        <button class="v6-button v6-button--outline v6-nav-toggle" type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="v6-main-nav">
            Menü
        </button>

        <nav id="v6-main-nav" class="v6-main-nav" :class="{ 'is-open': open }" aria-label="Fő navigáció">
            <a href="{{ route('home') }}">Főoldal</a>
            @auth
                <a href="{{ route('orderpage') }}">Webshop</a>
                <a class="v6-header-action" href="{{ route('cart') }}">Kosár <span class="v6-badge">{{ auth()->user()->cart?->items()->sum('quantity') ?? 0 }}</span></a>
                <a href="{{ route('account') }}">Fiók</a>
            @else
                <a href="{{ route('login') }}">Belépés</a>
            @endauth
            <a href="{{ route('contact') }}">Kapcsolat</a>
            <a href="{{ route('info', ['slug' => 'gyik']) }}">GYIK</a>
        </nav>
    </div>
</header>
