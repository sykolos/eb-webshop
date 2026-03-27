@extends('layouts.admin')
@section('title','Árazás')
@section('content')
<?php
// Sort users alphabetically by name
$sortedUsers = collect($users)->sortBy('name');
$sortedCategories = collect($categories)->sortBy('name');
?>
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold">Árazási Mátrix</h5>
            <div class="btn-group btn-group-sm" role="group" id="modeToggle">
                <button class="btn btn-light active" type="button" data-mode="user">Ügyfél mód</button>
                <button class="btn btn-light" type="button" data-mode="product">Termék mód</button>
            </div>
        </div>

        <div class="card-body">
            <div id="modeUserControls" class="d-flex align-items-center gap-2 mb-3">
                <select class="form-select select2-search" id="userSelect" style="min-width:250px;">
                    <option value="">-- Válassz ügyfelet --</option>
                    @foreach ($sortedUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <span class="text-muted">Keresés és szerkesztés ügyfélre szűrve</span>
            </div>
            <div id="modeProductControls" class="d-none d-flex align-items-center gap-2 mb-3">
                <select id="productSelect" class="form-select select2-search" style="min-width:280px;"></select>
                <span class="text-muted">Keresés termékre és nézd meg a felhasználókat</span>
            </div>

            <div id="mainLoader" class="text-center py-5" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Adatok szinkronizálása...</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-danger mb-4 shadow-sm">
    <div class="card-header bg-danger text-white py-2">
        <h6 class="m-0"><i class="fas fa-globe"></i> Globális kategória kedvezmény (Minden ügyfélre!)</h6>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="small fw-bold">Kategória kiválasztása</label>
                <select id="globalCategorySelect" class="form-select select2-search">
                    @foreach($sortedCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="small fw-bold">Új kedvezmény %</label>
                <input type="number" id="globalPercentValue" class="form-control" placeholder="Pl: 15">
            </div>
            <div class="col-12 col-md-3">
                <button id="btnGlobalApply" class="btn btn-danger w-100">
                    <i class="fas fa-sync"></i> Frissítés mindenkinek
                </button>
            </div>
        </div>
    </div>
</div>

<div id="controlsWrapper" style="display:none;" class="row mb-4 bg-light p-3 rounded shadow-sm g-3">
    <div class="col-12 col-md-2">
        <label class="small fw-bold">Keresés</label>
        <input type="text" id="productSearch" class="form-control" placeholder="Név vagy cikkszám...">
    </div>
    <div class="col-12 col-md-3">
        <label class="small fw-bold">Kategória szűrő</label>
        <select id="categoryFilter" class="form-select select2-search">
            <option value="">Összes kategória</option>
            @foreach($sortedCategories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-3 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="onlyModifiedFilter">
            <label class="form-check-label fw-bold" for="onlyModifiedFilter">Csak módosított termékek</label>
        </div>
    </div>
</div>

<div id="tableWrapper" style="display:none;">
    <div class="table-responsive">
        <table class="table table-hover align-middle border" id="priceTable">
            <thead class="table-dark">
                <tr>
                    <th>Termék adatok</th>
                    <th>Alapár</th>
                    <th style="min-width: 100px;">Kategória %</th>
                    <th style="min-width: 100px;">Extra %</th>
                    <th class="text-info">Kalkulált ár</th>
                    <th style="min-width: 150px;">Egyedi Fix Ár</th>
                    <th>Megjelenített Ár</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="priceTableBody"></tbody>
        </table>
    </div>
    <div id="paginationWrapper" class="d-flex justify-content-center mt-4"></div>
</div>

<div id="userTableWrapper" style="display:none;">
    <div class="table-responsive">
        <table class="table table-hover align-middle border" id="userTable">
            <thead class="table-dark">
                <tr>
                    <th>Ügyfél</th>
                    <th>Email</th>
                    <th>Módosított ár?</th>
                    <th>Fix ár</th>
                </tr>
            </thead>
            <tbody id="userTableBody"></tbody>
        </table>
    </div>
    <div id="userPaginationWrapper" class="d-flex justify-content-center mt-4"></div>
</div>

<style>
    .calculated-price { font-weight: bold; color: #0d6efd; transition: all 0.3s; }
    .row-has-fix-price { background-color: rgba(13, 110, 253, 0.05); }
    .price-input-group { position: relative; }
    .modified-badge { position: absolute; top: -10px; right: -5px; font-size: 10px; }

    /* Special prices sizing adjustments */
    #userSelect, #userSelect + .select2-container {
        max-width: 280px !important;
    }

    #productSearch {
        max-width: 240px;
    }

    @media (max-width: 768px) {
        #productSearch {
            max-width: none;
        }
    }
</style>

<script>
$(document).ready(function() {
    let mode = 'user';
    let selectedUser = null;
    let selectedProduct = null;

    // Initialize Select2 for all searchable selects if plugin is loaded
    if (typeof $.fn.select2 === 'function') {
        $('.select2-search').select2({
            allowClear: true,
            theme: 'bootstrap-5',
            language: 'hu',
            width: '100%',
            placeholder: 'Keresés...'
        });

        $('#productSelect').select2({
        ajax: {
            url: '{{ route("adminpanel.special_prices.product_select") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { search: params.term };
            },
            processResults: function(data) {
                return { results: data.results };
            }
        },
        theme: 'bootstrap-5',
        placeholder: '-- Válassz terméket --',
        allowClear: true,
        width: '100%'
    });
    } else {
        console.warn('Select2 plugin is not available; referring to native select fields');
    }

    // Mode switcher
    $('#modeToggle button').on('click', function() {
        mode = $(this).data('mode');
        $('#modeToggle button').removeClass('active');
        $(this).addClass('active');
        applyModeState();
    });

    function applyModeState() {
        if (mode === 'user') {
            $('#modeUserControls').removeClass('d-none');
            $('#modeProductControls').addClass('d-none');
            $('#categoryFilter').parent().show();
            $('#priceTableWrapper').show();
            $('#userTableWrapper').hide();
            if (selectedUser) {
                loadMatrix(1);
            } else {
                $('#tableWrapper, #controlsWrapper').hide();
            }
        } else {
            $('#modeUserControls').addClass('d-none');
            $('#modeProductControls').removeClass('d-none');
            $('#categoryFilter').parent().hide();
            $('#tableWrapper').hide();
            $('#userTableWrapper').show();
            if (selectedProduct) {
                loadUsersByProduct(1);
            } else {
                $('#userTableWrapper').hide();
            }
        }
        persistUrlState();
    }

    function persistUrlState() {
        const url = new URL(window.location);
        url.searchParams.set('mode', mode);
        if (mode === 'user') {
            if (selectedUser) url.searchParams.set('user', selectedUser);
            else url.searchParams.delete('user');
            url.searchParams.delete('product');
        } else {
            if (selectedProduct) url.searchParams.set('product', selectedProduct);
            else url.searchParams.delete('product');
            url.searchParams.delete('user');
        }
        const search = $('#productSearch').val();
        if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
        const modified = $('#onlyModifiedFilter').is(':checked') ? 1 : 0;
        url.searchParams.set('modified', modified);
        window.history.replaceState({}, '', url);
    }

    // --- Load state from URL ---
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('mode') && urlParams.get('mode') === 'product') {
        mode = 'product';
        $('#modeToggle button[data-mode="product"]').trigger('click');
    }

    if (urlParams.has('user')) {
        selectedUser = urlParams.get('user');
        $('#userSelect').val(selectedUser).trigger('change');
    }

    if (urlParams.has('product')) {
        selectedProduct = urlParams.get('product');
        $('#productSelect').append(new Option('Betöltött termék', selectedProduct, true, true)).trigger('change');
    }

    if (urlParams.has('search')) {
        $('#productSearch').val(urlParams.get('search'));
    }

    if (urlParams.get('modified') == '1') {
        $('#onlyModifiedFilter').prop('checked', true);
    }
    
    let initialPage = urlParams.get('page') || 1;
    // --------------------------------

    function initializeUser() {
        if (selectedUser) {
            $('#mainLoader').show();
            $('#controlsWrapper, #tableWrapper').hide();
            loadMatrix(initialPage);
        } else {
            $('#controlsWrapper, #tableWrapper').hide();
        }
    }

    function initializeProduct() {
        if (selectedProduct) {
            $('#mainLoader').show();
            $('#userTableWrapper').hide();
            loadUsersByProduct(initialPage);
        } else {
            $('#userTableWrapper').hide();
        }
    }

    $('#userSelect').on('change', function() {
        selectedUser = $(this).val();
        if (selectedUser) {
            mode = 'user';
            $('#modeToggle button[data-mode="user"]').trigger('click');
            initializeUser();
        } else {
            $('#tableWrapper').hide();
        }
    });

    $('#productSelect').on('change', function() {
        selectedProduct = $(this).val();
        if (selectedProduct) {
            mode = 'product';
            $('#modeToggle button[data-mode="product"]').trigger('click');
            initializeProduct();
        } else {
            $('#userTableWrapper').hide();
        }
    });

    function loadMatrix(page = 1) {
        if (!selectedUser) return;

        let search = $('#productSearch').val();
        let catId = $('#categoryFilter').val();
        let onlyModified = $('#onlyModifiedFilter').is(':checked') ? 1 : 0;

        // Update URL
        const url = new URL(window.location);
        url.searchParams.set('mode', 'user');
        url.searchParams.set('user', selectedUser);
        url.searchParams.set('page', page);
        url.searchParams.set('search', search);
        url.searchParams.set('category', catId);
        url.searchParams.set('modified', onlyModified);
        window.history.replaceState({}, '', url);

        $.get(`/adminpanel/special-prices/matrix-ajax/${selectedUser}`, {
            page: page,
            search: search,
            category_id: catId,
            only_modified: onlyModified
        }, function(data) {
            $('#priceTableBody').empty();

            data.products.data.forEach(p => {
                let catDisc = p.category_discount?.discount_percent || 0;
                let specialData = p.special_prices.length > 0 ? p.special_prices[0] : null;
                let extraDisc = specialData?.extra_percent || 0;
                let fixPrice = specialData?.price || '';
                let basePrice = parseFloat(p.price);
                let calculated = Math.round(basePrice * (1 - ((parseFloat(catDisc) + parseFloat(extraDisc)) / 100)));

                let row = `
                    <tr data-id="${p.id}" data-base-price="${p.price}" data-cat-id="${p.category_id}" class="${fixPrice ? 'table-info' : ''}">
                        <td><div class="fw-bold">${p.title}</div><code class="small text-muted">${p.serial_number || '-'}</code></td>
                        <td><span class="text-muted small">${p.price} Ft</span></td>
                        <td><input type="number" class="form-control form-control-sm cat-disc-input" value="${catDisc}"></td>
                        <td><input type="number" class="form-control form-control-sm extra-disc-input" value="${extraDisc}"></td>
                        <td class="calculated-price text-secondary">${calculated} Ft</td>
                        <td><div class="input-group input-group-sm"><input type="number" class="form-control fix-price-input" value="${fixPrice}"></div></td>
                        <td class="fw-bold text-success final-display-price">${fixPrice ? fixPrice : calculated} Ft</td>
                        <td><button class="btn btn-sm btn-success btn-save-row px-3 w-100"><i class="fas fa-save"></i> Mentés</button></td>
                    </tr>`;
                $('#priceTableBody').append(row);
            });

            renderPagination(data.products);
            $('#mainLoader').hide();
            $('#controlsWrapper, #tableWrapper').fadeIn();
        });
    }

    function loadUsersByProduct(page = 1) {
        if (!selectedProduct) return;

        let search = $('#productSearch').val();
        let onlyModified = $('#onlyModifiedFilter').is(':checked') ? 1 : 0;

        const url = new URL(window.location);
        url.searchParams.set('mode', 'product');
        url.searchParams.set('product', selectedProduct);
        url.searchParams.set('page', page);
        url.searchParams.set('search', search);
        url.searchParams.set('modified', onlyModified);
        window.history.replaceState({}, '', url);

        $.get(`/adminpanel/special-prices/product-users-ajax/${selectedProduct}`, {
            page: page,
            search: search,
            only_modified: onlyModified
        }, function(data) {
            $('#userTableBody').empty();

            data.users.data.forEach(u => {
                const special = u.special_prices.length > 0 ? u.special_prices[0] : null;
                let altered = special ? '<span class="badge bg-success">Igen</span>' : '<span class="badge bg-secondary">Nem</span>';
                let fixedPrice = special ? special.price + ' Ft' : '-';

                let row = `
                    <tr data-id="${u.id}">
                        <td>${u.name}</td>
                        <td>${u.email}</td>
                        <td>${altered}</td>
                        <td>${fixedPrice}</td>
                    </tr>`;
                $('#userTableBody').append(row);
            });

            renderUserPagination(data.users);
            $('#mainLoader').hide();
            $('#userTableWrapper').fadeIn();
        });
    }

    function renderPagination(data) {
        let html = '<ul class="pagination pagination-sm">';
        for (let i = 1; i <= data.last_page; i++) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        html += '</ul>';
        $('#paginationWrapper').html(html);
    }

    function renderUserPagination(data) {
        let html = '<ul class="pagination pagination-sm">';
        for (let i = 1; i <= data.last_page; i++) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        html += '</ul>';
        $('#userPaginationWrapper').html(html);
    }

    $(document).on('click', '#paginationWrapper .page-link', function(e) {
        e.preventDefault();
        loadMatrix($(this).data('page'));
    });

    $(document).on('click', '#userPaginationWrapper .page-link', function(e) {
        e.preventDefault();
        loadUsersByProduct($(this).data('page'));
    });

    $('#productSearch').on('input', function() {
        delay(function(){
            if (mode === 'user') loadMatrix(1); else loadUsersByProduct(1);
        }, 500);
    });

    $('#categoryFilter').on('change', function() {
        if (mode === 'user') loadMatrix(1);
    });

    $('#onlyModifiedFilter').on('change', function() {
        if (mode === 'user') loadMatrix(1); else loadUsersByProduct(1);
    });

    // original price update and save behaviors remain unchanged only for user mode

    function updateRowPrices(row) {
        let base = parseFloat(row.data('base-price'));
        let d1 = parseFloat(row.find('.cat-disc-input').val()) || 0;
        let d2 = parseFloat(row.find('.extra-disc-input').val()) || 0;
        let fix = row.find('.fix-price-input').val();

        let calc = Math.round(base * (1 - ((d1 + d2) / 100)));
        row.find('.calculated-price').text(calc + ' Ft');

        let final = (fix && fix > 0) ? fix : calc;
        row.find('.final-display-price').text(final + ' Ft');

        if (fix > 0) row.addClass('table-info'); else row.removeClass('table-info');
    }

    $(document).on('input', '.cat-disc-input, .extra-disc-input, .fix-price-input', function() {
        if (mode === 'user') updateRowPrices($(this).closest('tr'));
    });

    $(document).on('click', '.btn-save-row', function() {
        if (mode !== 'user') return;
        let row = $(this).closest('tr');
        let catId = row.data('cat-id');
        let newCatDisc = row.find('.cat-disc-input').val() || 0;
        let newExtraDisc = row.find('.extra-disc-input').val() || 0;

        let postData = {
            _token: '{{ csrf_token() }}',
            user_id: selectedUser,
            product_id: row.data('id'),
            category_id: catId,
            discount_percent: newCatDisc,
            extra_percent: newExtraDisc,
            fix_price: row.find('.fix-price-input').val()
        };

        $.post('/adminpanel/special-prices/save-matrix', postData, function(response) {
            $(`#priceTableBody tr[data-cat-id="${catId}"]`).each(function() {
                let currentRow = $(this);
                currentRow.find('.cat-disc-input').val(newCatDisc);
                updateRowPrices(currentRow);

                if (currentRow.data('id') == postData.product_id) {
                    currentRow.addClass('table-success');
                    setTimeout(() => currentRow.removeClass('table-success'), 1000);
                }
            });
        });
    });

    // Global category updates preserved as-is;
    $('#btnGlobalApply').on('click', function() {
        const catId = $('#globalCategorySelect').val();
        const catName = $('#globalCategorySelect option:selected').text();
        const percent = $('#globalPercentValue').val();

        if (!percent || percent < 0 || percent > 100) {
            alert('Kérlek adj meg egy érvényes százalékot (0-100)!');
            return;
        }

        const confirmMsg = `FIGYELEM!\n\nEz a művelet az ÖSSZES ügyfélnél átállítja a(z) "${catName}" kategória kedvezményét ${percent}%-ra.\n\nBiztosan folytatod?`;

        if (confirm(confirmMsg)) {
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Dolgozom...');

            $.post('/adminpanel/special-prices/global-category-update', {
                _token: '{{ csrf_token() }}',
                category_id: catId,
                discount_percent: percent
            })
            .done(function(response) {
                alert(response.message);
                if ($('#categoryFilter').val() == catId || $('#categoryFilter').val() == "") {
                    if (mode === 'user') loadMatrix(1); else loadUsersByProduct(1);
                }
            })
            .fail(function() {
                alert('Hiba történt a mentés során!');
            })
            .always(function() {
                $('#btnGlobalApply').prop('disabled', false).html('<i class="fas fa-sync"></i> Frissítés mindenkinek');
            });
        }
    });

    function delay(callback, ms) {
        clearTimeout(window._priceDelayTimer);
        window._priceDelayTimer = setTimeout(callback, ms);
    }

    applyModeState();
});
</script>
@endsection