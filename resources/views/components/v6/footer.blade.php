<footer class="v6-site-footer">
    <div class="v6-container v6-footer-grid">
        <div>
            <h2>{{ config('site.company.name') }}</h2>
            <p>{{ config('site.company.tagline') }}</p>
        </div>
        <div class="v6-footer-links">
            <h3>Navigáció</h3>
            <a href="{{ route('home') }}">Főoldal</a>
            <a href="{{ route('contact') }}">Kapcsolat</a>
            <a href="{{ route('info', ['slug' => 'szallitas']) }}">Szállítás és fizetés</a>
            <a href="{{ route('info', ['slug' => 'katalogus']) }}">Katalógus</a>
            <a href="{{ route('impresszum') }}">Impresszum</a>
            <a href="{{ route('aszf') }}">ÁSZF</a>
            <a href="{{ route('adatkezelesi-nyilatkozat') }}">Adatkezelés</a>
            <a href="{{ route('elallasi-nyilatkozat') }}">Elállás</a>
            <a href="{{ route('login') }}">Bejelentkezés</a>
        </div>
        <div>
            <h3>Kapcsolat</h3>
            <p>{{ config('site.company.address') }}</p>
            <p><a href="mailto:{{ config('site.company.email') }}">{{ config('site.company.email') }}</a></p>
            @foreach(config('site.company.phones') as $phone)<p><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></p>@endforeach
        </div>
    </div>
    <div class="v6-container v6-footer-bottom">© {{ date('Y') }} Electro Business Kft.</div>
</footer>
