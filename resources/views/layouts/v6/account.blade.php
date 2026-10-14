<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Partnerfiók') · Electro Business</title>
    @vite('resources/js/v6.js')
</head>
<body class="v6-body">
    <a class="v6-skip-link" href="#main-content">Ugrás a tartalomhoz</a>
    @include('components.v6.header')
    <main id="main-content" class="v6-container v6-account-layout">
        <aside class="v6-account-nav" aria-label="Fiók navigáció">
            <h2>Partnerfiók</h2>
            <a href="{{ route('adminpanel.design-system.account') }}" aria-current="page">Áttekintés</a>
            <a href="{{ route('account.orders') }}">Rendeléseim</a>
            <a href="{{ route('account.userinfo') }}">Cégadatok</a>
            <a href="{{ route('account.modify') }}">Beállítások</a>
        </aside>
        <section>@yield('content')</section>
    </main>
    @include('components.v6.footer')
</body>
</html>
