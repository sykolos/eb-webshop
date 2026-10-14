<header class="hero-header">
    <img src="{{ asset('img/electrobusiness-hero.png') }}" class="hero-banner" alt="ElectroBusiness ipari termékkínálat">
    <div class="hero-overlay">
        <div class="hero-content container">
            <p class="hero-kicker">Courbi márkaképviselet</p>
            <h1 class="hero-title">Biztos kapcsolat.<br>Erős alapok.</h1>
            <p class="hero-subtitle">Ipari és villamossági termékek partnereknek, átlátható katalógussal és aktuális elérhetőséggel.</p>
            <div class="hero-actions">
                <a href="{{ route('orderpage') }}" class="btn btn-primary btn-lg">Termékek böngészése <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">Partner belépés</a>
                @else
                    <a href="{{ route('account') }}" class="btn btn-outline-light btn-lg">Fiókom</a>
                @endguest
            </div>
            <div class="hero-trust-row" aria-label="Szolgáltatási előnyök">
                <span><i class="bi bi-box-seam" aria-hidden="true"></i> Aktuális elérhetőség</span>
                <span><i class="bi bi-tag" aria-hidden="true"></i> Partnerárak</span>
                <span><i class="bi bi-headset" aria-hidden="true"></i> Szakértői támogatás</span>
            </div>
        </div>
    </div>
</header>
