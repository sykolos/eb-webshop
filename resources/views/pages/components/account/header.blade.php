<aside class="account-v6-sidebar">
    <div class="account-v6-profile">
        <span class="v6-eyebrow">PARTNERFÓKÓK</span>
        <h2>{{ auth()->user()->name }}</h2>
        <p>{{ auth()->user()->email }}</p>
        @if(auth()->user()->hasVerifiedEmail())
            <span class="v6-status v6-status--success">Email hitelesítve</span>
        @else
            <span class="v6-status v6-status--warning">Email nincs hitelesítve</span>
            <form action="{{ route('verification.send') }}" method="post" class="account-v6-inline-form">@csrf<button class="v6-button v6-button--small v6-button--outline" type="submit">Email újraküldése</button></form>
        @endif
    </div>
    <nav class="account-v6-nav" aria-label="Partnerfiók navigáció">
        <a href="{{ route('account') }}">Áttekintés</a>
        <a href="{{ route('account.orders') }}">Rendeléseim</a>
        <a href="{{ route('account.userinfo') }}">Cégadatok</a>
        <a href="{{ route('account.favorites') }}">Kedvencek</a>
        <a href="{{ route('account.modify') }}">Beállítások</a>
        <a href="{{ route('account.tickets') }}">Ügyfélszolgálat</a>
    </nav>
</aside>
