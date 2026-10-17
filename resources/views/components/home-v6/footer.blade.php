<footer class="home-v6-footer">
    <div class="v6-container home-v6-footer__grid">
        <div>
            <img class="home-v6-footer__logo" src="{{ asset('img/logo.svg') }}" alt="Electro Business">
            <p>{{ config('site.company.tagline') }}</p>
            <p class="home-v6-footer__muted">Szakmai beszerzés átláthatóan, partnerként.</p>
        </div>
        <div>
            <h2>Webshop</h2>
            <a href="{{ route('orderpage') }}">Minden termék</a>
            <a href="{{ route('orderpage', ['category' => 'latest']) }}">Újdonságok</a>
            <a href="{{ route('info.offers') }}">Akciók</a>
            <a href="{{ route('register') }}">Partnerprogram</a>
        </div>
        <div>
            <h2>Információk</h2>
            <a href="{{ route('info', ['slug' => 'szallitas']) }}">Szállítás és fizetés</a>
            <a href="{{ route('info', ['slug' => 'gyik']) }}">Gyakori kérdések</a>
            <a href="{{ route('info', ['slug' => 'katalogus']) }}">Katalógus</a>
            <a href="{{ route('aszf') }}">ÁSZF</a>
            <a href="{{ route('adatkezelesi-nyilatkozat') }}">Adatkezelés</a>
        </div>
        <div>
            <h2>Kapcsolat</h2>
            <p>{{ config('site.company.address') }}</p>
            <a href="mailto:{{ config('site.company.email') }}">{{ config('site.company.email') }}</a>
            @foreach(config('site.company.phones') as $phone)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a>
            @endforeach
            <span class="home-v6-footer__muted">{{ config('site.company.opening_hours') }}</span>
        </div>
    </div>
    <div class="v6-container home-v6-footer__bottom">
        <span>© {{ date('Y') }} Electro Business Kft.</span>
        <span>Biztos kapcsolat. Erős alapok.</span>
    </div>
</footer>
