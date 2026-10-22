<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'Villamossági alapanyagok szakembereknek, partnerárakkal és átlátható katalógussal.' }}">
    <title>@yield('title', 'Electro Business')</title>
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    @vite('resources/js/home-v6.js')
</head>
<body class="home-v6-body">
    <a class="v6-skip-link" href="#main-content">Ugrás a tartalomhoz</a>
    @include('components.home-v6.header', ['headerCategories' => $topCategories ?? collect()])
    <main id="main-content">@yield('content')</main>
    @include('components.home-v6.footer')
    @include('components.consent')
    <button class="eb-consent-settings" type="button" data-consent-settings>Cookie beállítások</button>
</body>
</html>
