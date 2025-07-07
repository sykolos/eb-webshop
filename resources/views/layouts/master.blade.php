<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name=”description” content=”Villanyszerelési eszközök kis és nagykereskedelme.” />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{asset('img/logo.png')}}">
    <link rel="apple-touch-icon" href="{{asset('img/logo.png')}}">
    <link rel="shortcut icon" href="{{asset('img/logo.png')}}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css">
    {{-- <link rel="stylesheet" href="{{asset('css/app.css?v=1')}}"> --}}
    @vite(['resources/sass/app.scss'])

</head>
{{-- 
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-K8V2GXMWFW"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-K8V2GXMWFW');
</script> --}}

<body>
    @include('layouts.partials.nav')
    
    @include('cookie-consent::index')
    <main class="page bg-light">        
        @yield('content')
    </main>
    @include('layouts.partials.footer')
    </body>
    @stack('scripts')
</html>