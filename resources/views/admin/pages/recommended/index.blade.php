@extends('layouts.admin')
@section('title', 'Kiemelt termékek')
@section('content')

<h1 class="page-title">Kiemelt termékek</h1>

<div class="container">
    <div class="col-12"> 
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>Kiemelt termékek kiválasztása</h5>
            </div>

            <div class="card-body">

                <form id="filter-form" class="mb-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <input type="text" name="q" class="form-control" placeholder="Keresés cikkszámra vagy névre...">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Keresés</button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('adminpanel.recommended.update') }}">
                    @csrf
                    <div id="loading-indicator" class="mb-3 text-muted" style="display:none;">
                        <em>Betöltés...</em>
                    </div>

                    <div id="product-table">
                        @include('admin.pages.recommended.table', ['products' => $products, 'recommendedIds' => $recommendedIds])
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">Mentés</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('filter-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const url = "{{ route('adminpanel.recommended.ajax') }}?" + new URLSearchParams(new FormData(form)).toString();

    // mutatjuk a "Betöltés..." feliratot
    document.getElementById('loading-indicator').style.display = 'block';

    fetch(url)
        .then(res => res.json())
        .then(data => {
            document.getElementById('product-table').innerHTML = data.html;
        })
        .catch(error => {
            console.error('Hiba:', error);
            document.getElementById('product-table').innerHTML = '<div class="text-danger">Hiba történt az adatok betöltésekor.</div>';
        })
        .finally(() => {
            document.getElementById('loading-indicator').style.display = 'none';
        });
});
</script>


@endsection
