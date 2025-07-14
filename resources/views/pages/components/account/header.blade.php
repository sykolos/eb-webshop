@if (!isset(auth()->user()->user_invoice->company_name))
    <script>alert('Fontos! Töltsd ki a felhasználó adataidat mindenek előtt.')</script>
@endif

<section class="user-box bg-dark text-light p-4 rounded shadow-sm d-flex flex-column flex-start">
    {{-- Profil kép --}}
    {{-- Felhasználónév --}}
    <p class="fs-5 fw-bold mb-2">🧑 {{ auth()->user()->name }}</p>

    {{-- Email --}}
    <p class="mb-0 fst-italic">✉️ {{ auth()->user()->email }}</p>

    {{-- Hitelesítés státusz --}}
    @if (auth()->user()->hasVerifiedEmail())
        <p class="text-success small mb-2">✔️ hitelesítve</p>
    @else
        <p class="text-warning small mb-2">❌ nincs hitelesítve</p>
        <form action="{{ route('verification.send') }}" method="post">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light mb-3">Email újraküldés</button>
        </form>
    @endif

    {{-- Cégnév --}}
    <p class="mb-2">🏢 {{ auth()->user()->user_invoice->company_name ?? 'Nincs megadva cégnév' }}</p>

    {{-- Összes rendelés --}}
    <p class="mb-4">📦 Összes rendelés: <strong>{{ auth()->user()->orders->count() }}</strong></p>

    {{-- Gombok --}}
    <div class="d-grid gap-2 w-100">
        <a class="btn btn-primary btn-lg px-4 bg-gradient" href="{{ route('account.orders') }}">Rendelések</a>
        <a class="btn btn-primary btn-lg px-4 bg-gradient" href="{{ route('account.userinfo') }}">Fh. Adatok</a>
    </div>
</section>