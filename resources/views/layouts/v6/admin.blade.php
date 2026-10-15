<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Adminpanel') · Electro Business</title>
    @vite('resources/js/v6.js')
</head>
<body class="v6-body" x-data="{ sidebarOpen: false }">
    <div class="v6-admin-shell">
        <aside class="v6-admin-sidebar" :class="{ 'is-open': sidebarOpen }" aria-label="Admin navigáció">
            <a class="v6-brand" href="{{ route('adminpanel.design-system') }}"><img src="{{ asset('img/logo.svg') }}" alt=""><span>ADMINPANEL</span></a>
            <nav>
                <a href="{{ route('adminpanel') }}">Vezérlőpult</a>
                <a href="{{ route('adminpanel.products') }}">Termékek</a>
                <a href="{{ route('adminpanel.categories') }}">Kategóriák</a>
                <a href="{{ route('adminpanel.units') }}">Mennyiségi egységek</a>
                <a href="{{ route('adminpanel.orders') }}">Rendelések</a>
                <a href="{{ route('adminpanel.users') }}">Felhasználók</a>
                <a href="{{ route('adminpanel.special_prices') }}">Külön árak</a>
                <a href="{{ route('adminpanel.exports') }}">Exportok</a>
                <a href="{{ route('adminpanel.design-system') }}" aria-current="page">V6 komponensek</a>
            </nav>
            <div class="v6-admin-sidebar__bottom">
                <a href="{{ route('home') }}">Vissza a webshophoz</a>
                <form action="{{ route('logout') }}" method="post">@csrf<button class="v6-button v6-button--outline v6-button--small" type="submit">Kijelentkezés</button></form>
            </div>
        </aside>
        <main class="v6-admin-main">
            <div class="v6-admin-topbar">
                <div>
                    <button class="v6-button v6-button--outline v6-admin-mobile-toggle" type="button" x-on:click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen.toString()">Menü</button>
                    <h1>@yield('heading', 'Adminpanel')</h1>
                    @hasSection('intro')<p>@yield('intro')</p>@endif
                </div>
                <span class="v6-status v6-status--success">Staging preview</span>
            </div>
            @if(session('success'))<div class="v6-toast">{{ session('success') }}</div>@endif
            @yield('content')
        </main>
    </div>
</body>
</html>
