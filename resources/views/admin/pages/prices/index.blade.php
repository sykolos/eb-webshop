@extends('layouts.admin')
@section('title','Árazás')
@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold">Árazási Mátrix</h5>
            <select class="form-select w-25" id="userSelect">
                <option value="">-- Válassz ügyfelet --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="card-body">
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
                <select id="globalCategorySelect" class="form-select">
                    @foreach($categories as $cat)
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
    <div class="col-12 col-md-3">
        <label class="small fw-bold">Keresés</label>
        <input type="text" id="productSearch" class="form-control" placeholder="Név vagy cikkszám...">
    </div>
    <div class="col-12 col-md-3">
        <label class="small fw-bold">Kategória szűrő</label>
        <select id="categoryFilter" class="form-select">
            <option value="">Összes kategória</option>
            @foreach($categories as $cat)
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

<style>
    .calculated-price { font-weight: bold; color: #0d6efd; transition: all 0.3s; }
    .row-has-fix-price { background-color: rgba(13, 110, 253, 0.05); }
    .price-input-group { position: relative; }
    .modified-badge { position: absolute; top: -10px; right: -5px; font-size: 10px; }
</style>

<script>
$(document).ready(function() {
    let selectedUser = null;

    // --- NEW: Load state from URL ---
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('user')) {
        $('#userSelect').val(urlParams.get('user')); 
        selectedUser = urlParams.get('user');
        initializeUser();
        // .change() triggers your existing logic to show the table
    }
    
    if (urlParams.has('search')) $('#productSearch').val(urlParams.get('search'));
    if (urlParams.has('category')) $('#categoryFilter').val(urlParams.get('category'));
    if (urlParams.get('modified') == '1') $('#onlyModifiedFilter').prop('checked', true);
    
    let initialPage = urlParams.get('page') || 1;
    // --------------------------------

    // 1. Ügyfél választás
    $('#userSelect').on('change', function() {
        selectedUser = $(this).val();
        initializeUser();
    });

    function initializeUser() {
        if (selectedUser) {
            $('#controlsWrapper, #tableWrapper').hide();
            $('#mainLoader').show();
            // Check if there is a page in the URL, otherwise use 1
            const urlParams = new URLSearchParams(window.location.search);
            let targetPage = urlParams.get('page') || 1;
            loadMatrix(1);
        } else {
            $('#controlsWrapper, #tableWrapper').hide();
        }
    }


    // 2. Mátrix betöltése (Keresés, Szűrés, Lapozás)
    function loadMatrix(page = 1) {
        if (!selectedUser) return;

        let search = $('#productSearch').val();
        let catId = $('#categoryFilter').val();
        let onlyModified = $('#onlyModifiedFilter').is(':checked') ? 1 : 0;

        // --- NEW: Update the URL string ---
        const url = new URL(window.location);
        url.searchParams.set('user', selectedUser);
        url.searchParams.set('page', page);
        url.searchParams.set('search', search);
        url.searchParams.set('category', catId);
        url.searchParams.set('modified', onlyModified);
        window.history.replaceState({}, '', url); // Updates URL without reload
        // ----------------------------------

        $.get(`/adminpanel/special-prices/matrix-ajax/${selectedUser}`, {
            page: page,
            search: search,
            category_id: catId,
            only_modified: onlyModified
        }, function(data) {
            $('#priceTableBody').empty();
            
            data.products.data.forEach(p => {
                let catDisc = p.category_discount?.discount_percent || 0;
                // Javítás: Az extra százalékot a special_prices-ból vegyük, ha ott van
                let specialData = p.special_prices.length > 0 ? p.special_prices[0] : null;
                let extraDisc = specialData?.extra_percent || 0;
                let fixPrice = specialData?.price || '';
                
                let basePrice = parseFloat(p.price);
                let calculated = Math.round(basePrice * (1 - ((parseFloat(catDisc) + parseFloat(extraDisc)) / 100)));

                let row = `
                    <tr data-id="${p.id}" data-base-price="${p.price}" data-cat-id="${p.category_id}" class="${fixPrice ? 'table-info' : ''}">
                        <td>
                            <div class="fw-bold">${p.title}</div>
                            <code class="small text-muted">${p.serial_number || '-'}</code>
                        </td>
                        <td><span class="text-muted small">${p.price} Ft</span></td>
                        <td><input type="number" class="form-control form-control-sm cat-disc-input" value="${catDisc}"></td>
                        <td><input type="number" class="form-control form-control-sm extra-disc-input" value="${extraDisc}"></td>
                        <td class="calculated-price text-secondary">${calculated} Ft</td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control fix-price-input" value="${fixPrice}">
                            </div>
                        </td>
                        <td class="fw-bold text-success final-display-price">${fixPrice ? fixPrice : calculated} Ft</td>
                        <td>
                            <button class="btn btn-sm btn-success btn-save-row px-3 w-100">
                                <i class="fas fa-save"></i> Mentés
                            </button>
                        </td>
                    </tr>`;
                $('#priceTableBody').append(row);
            });
            
            renderPagination(data.products);
            $('#mainLoader').hide();
            $('#controlsWrapper, #tableWrapper').fadeIn();
        });
    }

    // 3. ESEMÉNYKEZELŐK A FRISSÍTÉSHEZ (Kereső és Szűrők)
    // A 'input' esemény jobb a keyup-nál, mert egérrel való beillesztésre is reagál
    $('#productSearch').on('input', function() {
        delay(function(){
            loadMatrix(1);
        }, 500 ); // 500ms várakozás, hogy ne küldjön minden betűnél kérést
    });

    $('#categoryFilter, #onlyModifiedFilter').on('change', function() {
        loadMatrix(1);
    });

    // 4. Számítások élőben
    function updateRowPrices(row) {
        let base = parseFloat(row.data('base-price'));
        let d1 = parseFloat(row.find('.cat-disc-input').val()) || 0;
        let d2 = parseFloat(row.find('.extra-disc-input').val()) || 0;
        let fix = row.find('.fix-price-input').val();
        
        let calc = Math.round(base * (1 - ((d1 + d2) / 100)));
        row.find('.calculated-price').text(calc + ' Ft');
        
        let final = (fix && fix > 0) ? fix : calc;
        row.find('.final-display-price').text(final + ' Ft');
        
        if(fix > 0) row.addClass('table-info'); else row.removeClass('table-info');
    }

    $(document).on('input', '.cat-disc-input, .extra-disc-input, .fix-price-input', function() {
        updateRowPrices($(this).closest('tr'));
    });

    // 5. Mentés
    $(document).on('click', '.btn-save-row', function() {
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
            // Frissítjük az összes azonos kategóriájú sort az oldalon
            $(`#priceTableBody tr[data-cat-id="${catId}"]`).each(function() {
                let currentRow = $(this);
                currentRow.find('.cat-disc-input').val(newCatDisc);
                updateRowPrices(currentRow);
                
                if(currentRow.data('id') == postData.product_id) {
                    currentRow.addClass('table-success');
                    setTimeout(() => currentRow.removeClass('table-success'), 1000);
                }
            });
        });
    });

    // Globális kategória frissítés eseménykezelő
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
                // Ha az aktuálisan nézett kategória megegyezik a frissítettel, töltsük újra a táblázatot
                if ($('#categoryFilter').val() == catId || $('#categoryFilter').val() == "") {
                    loadMatrix(1);
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

    // Segédfüggvény a lapozáshoz
    function renderPagination(data) {
        let html = '<ul class="pagination pagination-sm">';
        for (let i = 1; i <= data.last_page; i++) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }
        html += '</ul>';
        $('#paginationWrapper').html(html);
    }

    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        loadMatrix($(this).data('page'));
    });

    // Késleltetés (Debounce) funkció a kereséshez
    let delay = (function(){
        let timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
});
</script>
@endsection