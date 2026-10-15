<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="legacy-body">
    <div id="app">
        <header class="legacy-header">
            <div class="container legacy-header__inner">
                <a class="legacy-header__brand" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
                <nav aria-label="{{ __('Primary navigation') }}">
                    @guest
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}">{{ __('Login') }}</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">{{ __('Register') }}</a>
                        @endif
                    @else
                        <span>{{ Auth::user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="legacy-inline-form">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">{{ __('Logout') }}</button>
                        </form>
                    @endguest
                </nav>
            </div>
        </header>
        <main class="py-4">@yield('content')</main>
    </div>
</body>
</html>
