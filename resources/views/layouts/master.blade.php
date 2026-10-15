<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="description" content="Villanyszerelési eszközök kis és nagykereskedelme." />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="{{asset('img/logo.png')}}">
    <link rel="apple-touch-icon" href="{{asset('img/logo.png')}}">
    <link rel="shortcut icon" href="{{asset('img/logo.png')}}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/app.js')


</head>


<body>
    @include('layouts.partials.nav')
    
    <main class="page bg-light">
        @yield('content')
    </main>
    @include('layouts.partials.footer')
    @stack('scripts')
    @include('components.consent')
    <button class="eb-consent-settings" type="button" data-consent-settings>Cookie beállítások</button>
</body>
</html>
