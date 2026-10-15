<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Electro Business')</title>
    @vite('resources/js/v6.js')
</head>
<body class="v6-body">
    <a class="v6-skip-link" href="#main-content">Ugrás a tartalomhoz</a>
    @include('components.v6.header')
    <main id="main-content">@yield('content')</main>
    @include('components.v6.footer')
    @include('components.consent')
    <button class="eb-consent-settings" type="button" data-consent-settings>Cookie beállítások</button>
</body>
</html>
