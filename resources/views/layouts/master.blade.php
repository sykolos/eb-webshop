<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name=”description” content=”Villanyszerelési eszközök kis és nagykereskedelme.” />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="{{asset('img/logo.png')}}">
    <link rel="apple-touch-icon" href="{{asset('img/logo.png')}}">
    <link rel="shortcut icon" href="{{asset('img/logo.png')}}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @vite(['resources/sass/app.scss'])
    @vite('resources/js/app.js')
    <meta name="csrf-token" content="{{ csrf_token() }}">


</head>


<body>
    @include('layouts.partials.nav')
    
    <main class="page bg-light">        
        @yield('content')
    </main>
    @include('components.chatbot')
    @include('layouts.partials.footer')
    </body>
    @stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js"></script>
<script>
window.addEventListener("load", function(){
  window.cookieconsent.initialise({
    palette: {
      popup: { background: "#000", text: "#fff" },
      button: { background: "#f1d600", text: "#000" }
    },
    theme: "classic",
    type: "opt-in",
    position: "bottom",
    revokable: false, // <- kis sarokgomb tiltása
    dismissOnWindowClick: false,
    dismissOnScroll: false,
    content: {
      message: "Ez a weboldal sütiket használ a felhasználói élmény javításához.",
      dismiss: "Elfogadom",
      deny: "Elutasítom",
      link: "Adatkezelési tájékoztató",
      href: "/adatkezelesi-nyilatkozat"
    },
    onStatusChange: function(status) {
      if (status === 'allow') {
        console.log("Elfogadta a sütiket.");
        // pl. ide jöhet Google Analytics inicializálása
      } else if (status === 'deny') {
        console.log("Elutasította a sütiket.");
      }
    }
  });
});
</script>
<style>
.cc-revoke {
  display: none !important;
}
</style>
</html>