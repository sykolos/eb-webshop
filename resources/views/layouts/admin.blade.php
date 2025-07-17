<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <meta name= “robots” content=noindex, nofollow”>
    {{-- <link rel="stylesheet" href="{{asset('css/admin.css')}}"> --}}
    @vite(['resources/sass/admin.scss', 'resources/js/admin.js'])
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    <div class="admin-wrapper">
         <button class="burger-menu d-lg-none" id="toggle-sidebar">
            ☰
        </button>
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
    </div>
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const toggleBtn = document.getElementById("toggle-sidebar");
        const sidebar = document.getElementById("adminSidebar");
        const overlay = document.getElementById("sidebarOverlay");

        const openSidebar = () => {
            sidebar.classList.add("open");
            overlay.classList.add("active");
        };

        const closeSidebar = () => {
            sidebar.classList.remove("open");
            overlay.classList.remove("active");
        };

        if (toggleBtn && sidebar && overlay) {
            // Hamburger gombra kattintás
            toggleBtn.addEventListener("click", () => {
                if (sidebar.classList.contains("open")) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            // Overlay kattintásra zárás
            overlay.addEventListener("click", () => {
                closeSidebar();
            });

            // Mobilon linkre kattintásra zárás
            document.querySelectorAll('#adminSidebar a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 992) {
                        closeSidebar();
                    }
                });
            });
        }
    });
</script>

</body>
</html>