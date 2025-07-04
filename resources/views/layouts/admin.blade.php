<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <meta name= “robots” content=noindex, nofollow”>
    {{-- <link rel="stylesheet" href="{{asset('css/admin.css')}}"> --}}
    @vite(['resources/sass/admin.scss'])
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    @include('admin.partials.nav')
    
    <main class="admin-main bg-light">
        @if(session('success'))
    <div class="alert alert-success">
        {{session('success')}}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-success">
        {{session('error')}}
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-success">
        {{session('warning')}}
    </div>
    @endif
        @yield('content')
    </main>
</body>
</html>