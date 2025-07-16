<header class="position-relative bg-dark">
    <img src="{{ asset('img/courbi_banner.jpg') }}" class="w-100" alt="Courbi Banner" style="max-height: 550px; object-fit: cover;">

    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center text-white text-center" style="background: rgba(0,0,0,0.5);">
        <img src="{{ asset('img/eb_logo.png') }}" alt="Electro Business logo" class="mb-4" style="max-width: 200px;">
        <h1 class="display-6 fw-bold">Fedezze fel Courbi kínálatunkat</h1>
        <p class="lead">Nézze meg legújabb termékeinket most!</p>
        <a href="{{ route('orderpage') }}" class="btn btn-primary bg-gradient btn-lg mt-3">Termékek megtekintése</a>
    </div>
</header>