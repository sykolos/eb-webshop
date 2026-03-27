@extends('layouts.admin')
@section('title','Felhasználók')
@section('content')
<h1 class="page-title">Felhasználók</h1>
<div class="container">
    <div class="text-end mb-3">
        <a href="{{route('register')}}" class="btn btn-primary">Létrehozás</a>    
    </div>
    {{-- Szűrő card --}}
    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            <h5>Szűrés</h5>
        </div>
        
        <div class="card-body">
            <div class="mb-3">
                <form id="user-search-form">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Keresés név vagy email szerint">
                        <button type="submit" class="btn btn-dark">Keresés</button>
                    </div>
                </form>
            </div>

            <div id="spinner" class="text-center d-none">
                <div class="spinner-border" role="status"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12"> 
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Felhasználók</h5>
                </div>
                <div class="card-body">
                    
                            <div id="users-list">
                                @include('admin.pages.users.partials.list')
                            </div>
                            
                        
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('user-search-form');
    const listContainer = document.getElementById('users-list');
    const spinner = document.getElementById('spinner');

    // Load state from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) {
        form.querySelector('input[name="search"]').value = urlParams.get('search');
    }

    function fetchUsers(url = '{{ route("adminpanel.users") }}', page = 1) {
        spinner.classList.remove('d-none');

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        // Add page to params if not 1
        if (page > 1) {
            params.set('page', page);
        }

        // Update URL with current filters
        const url_obj = new URL(window.location);
        params.forEach((value, key) => {
            url_obj.searchParams.set(key, value);
        });
        window.history.replaceState({}, '', url_obj);

        fetch(url + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            listContainer.innerHTML = html;
            
            // Attach pagination handlers
            document.querySelectorAll('#users-list .pagination a').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const pageUrl = new URL(link.href);
                    const page = pageUrl.searchParams.get('page') || 1;
                    fetchUsers('{{ route("adminpanel.users") }}', page);
                });
            });
        })
        .finally(() => {
            spinner.classList.add('d-none');
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchUsers('{{ route("adminpanel.users") }}', 1);
    });

    // Debounce helper for search
    let searchTimeout;
    const searchInput = form.querySelector('input[name="search"]');
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchUsers('{{ route("adminpanel.users") }}', 1);
        }, 500);
    });
});
</script>
@endsection