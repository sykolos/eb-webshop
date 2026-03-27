@extends('layouts.admin')
@section('title', 'Kiemelt termékek')
@section('content')

<h1 class="page-title">Kiemelt termékek</h1>

<div class="container">
    <div class="card mb-4" id="live-box">
        <div class="card-header bg-dark primary text-white">
            <h5 class="mb-0">Kiemelt termékek élőben</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" id="recommended-live-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cikkszám</th>
                            <th>Megnevezés</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('adminpanel.recommended.update') }}">
        @csrf

        <div class="card">
            <div class="card-header bg-dark text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Terméklista</h5>
                    <input type="text" id="product-search" class="form-control w-50" placeholder="Keresés...">
                </div>
            </div>
            <div class="card-body" id="product-table">
                @include('admin.pages.recommended.table', ['products' => $products, 'recommendedIds' => $recommendedIds])
            </div>
        </div>

        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-success">Mentés</button>
        </div>
        <div id="hidden-selected-products"></div>

    </form>
</div>

<script>
window.recommendedIds = @json($recommendedIds);
window.recommendedData = @json($recommendedData);

$(document).ready(function () {
    let selectedProductIds = new Set(window.recommendedIds.map(id => id.toString()));

    // Load state from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) {
        $('#product-search').val(urlParams.get('search'));
    }
    
    // Load selected products from URL if present
    if (urlParams.has('selected')) {
        const selected = urlParams.get('selected').split(',').filter(id => id);
        selectedProductIds = new Set(selected);
    }

    function updateUrl() {
        const url = new URL(window.location);
        const searchQuery = $('#product-search').val();
        const selectedIds = Array.from(selectedProductIds).join(',');
        
        if (searchQuery) {
            url.searchParams.set('search', searchQuery);
        } else {
            url.searchParams.delete('search');
        }
        
        if (selectedIds) {
            url.searchParams.set('selected', selectedIds);
        } else {
            url.searchParams.delete('selected');
        }
        
        window.history.replaceState({}, '', url);
    }

    function logSelected(label = 'Aktuális lista') {
        console.log(`--- ${label} ---`);
        console.log('selectedProductIds:', Array.from(selectedProductIds));
    }

    function renderLiveList() {
        const $tbody = $('#recommended-live-table tbody');
        $tbody.empty();

        if (selectedProductIds.size === 0) {
            $tbody.append('<tr><td colspan="3" class="text-muted text-center">Nincs jelenleg kiemelt termék</td></tr>');
            return;
        }

        selectedProductIds.forEach(id => {
            let serial = '', title = '';

            // Asztali DOM-ból próbálja kiszedni
            const $row = $('.product-checkbox[value="' + id + '"]').closest('tr');
            if ($row.length) {
                serial = $row.find('td:nth-child(3)').text().trim();
                title = $row.find('td:nth-child(4)').text().trim();
            } else if (window.recommendedData[id]) {
                serial = window.recommendedData[id].serial_number;
                title = window.recommendedData[id].title;
            }

            $tbody.append(`<tr data-id="${id}"><td>${id}</td><td>${serial}</td><td>${title}</td></tr>`);
        });
    }

    function bindCheckboxes() {
        $('.product-checkbox').off('change').on('change', function () {
            const id = $(this).val();
            if ($(this).is(':checked')) {
                selectedProductIds.add(id);
            } else {
                selectedProductIds.delete(id);
            }

            // tükrözzük az állapotot az összes azonos értékű checkboxra (mobil + desktop)
            $('.product-checkbox[value="' + id + '"]').prop('checked', selectedProductIds.has(id));

            logSelected('Pipa változás után');
            renderLiveList();
            updateUrl();
        });
    }

    $('#recommended-live-table tbody').on('click', 'tr[data-id]', function () {
        const id = $(this).data('id').toString();
        selectedProductIds.delete(id);
        $('.product-checkbox[value="' + id + '"]').prop('checked', false);
        logSelected('Élő lista sorból törlés után');
        renderLiveList();
        updateUrl();
    });

    let searchTimeout;
    $('#product-search').on('input', function () {
        const query = $(this).val();
        
        // Update URL immediately with search query
        updateUrl();
        
        // Debounce the AJAX call
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            $.get('/adminpanel/recommended/ajax', { q: query }, function (data) {
                $('#product-table').html(data.html);
                
                // Restore checkbox states from selectedProductIds
                selectedProductIds.forEach(id => {
                    $('.product-checkbox[value="' + id + '"]').prop('checked', true);
                });
                
                bindCheckboxes();
                logSelected('AJAX újralekérés után');
                renderLiveList();
            });
        }, 300);
    });

    $('form').off('submit').on('submit', function () {
        const $container = $('#hidden-selected-products');
        $container.empty();

        const ids = Array.from(selectedProductIds);

        console.log('📤 Form elküldés: hidden input lista', ids);

        if (ids.length === 0) {
            $container.append('<input type="hidden" name="products[]" value="__empty__">');
        } else {
            ids.forEach(id => {
                $container.append('<input type="hidden" name="products[]" value="' + id + '">');
            });
        }
    });

    bindCheckboxes();
    logSelected('Inicializálás után');
    renderLiveList();
    updateUrl();
});
</script>


@endsection
