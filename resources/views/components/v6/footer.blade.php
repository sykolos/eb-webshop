<footer class="v6-site-footer">
    <div class="v6-container v6-footer-grid">
        <div>
            <h2>Electro Business Kft.</h2>
            <p>Villanyszerelési eszközök kis- és nagykereskedelme.</p>
        </div>
        <div class="v6-footer-links">
            <h3>Navigáció</h3>
            <a href="{{ route('home') }}">Főoldal</a>
            <a href="{{ route('contact') }}">Kapcsolat</a>
            <a href="{{ route('login') }}">Bejelentkezés</a>
        </div>
        <div>
            <h3>Kapcsolat</h3>
            <p>2600 Vác, Naszály út 18.</p>
            <p><a href="mailto:info@electrobusiness.hu">info@electrobusiness.hu</a></p>
            <p><a href="tel:+36202923769">+36 20 292 3769</a></p>
        </div>
    </div>
    <div class="v6-container v6-footer-bottom">© {{ date('Y') }} Electro Business Kft.</div>
</footer>
