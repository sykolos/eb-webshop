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

    function fetchUsers(url = '{{ route("adminpanel.users") }}') {
        spinner.classList.remove('d-none');

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        fetch(url + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            listContainer.innerHTML = html;
        })
        .finally(() => {
            spinner.classList.add('d-none');
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchUsers();
    });

   
});
</script>
@endsection