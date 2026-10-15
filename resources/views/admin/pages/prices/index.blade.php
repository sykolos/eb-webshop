@extends('layouts.admin')
@section('title', 'Árazás')
@section('content')
@php
    $sortedUsers = collect($users)->sortBy('name');
    $sortedCategories = collect($categories)->sortBy('name');
@endphp
<main class="container-fluid legacy-pricing" data-pricing-root>
    <section class="card shadow">
        <header class="card-header bg-dark text-white pricing-header">
            <h1>Árazási mátrix</h1>
            <div id="modeToggle" class="pricing-modes" role="group" aria-label="Árazási mód">
                <button class="btn btn-light active" type="button" data-mode="user">Ügyfél mód</button>
                <button class="btn btn-light" type="button" data-mode="product">Termék mód</button>
            </div>
        </header>
        <div class="card-body">
            <div id="modeUserControls" class="pricing-control-row">
                <label for="userSelect">Ügyfél</label>
                <select id="userSelect" class="form-select">
                    <option value="">-- Válassz ügyfelet --</option>
                    @foreach ($sortedUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div id="modeProductControls" class="pricing-control-row" hidden>
                <label for="productSelect">Termék azonosító</label>
                <input id="productSelect" class="form-control" inputmode="numeric" placeholder="Termék ID">
            </div>
            <p id="mainLoader" class="text-muted" hidden role="status">Adatok szinkronizálása...</p>
        </div>
    </section>

    <section class="card border-danger pricing-global">
        <div class="card-body">
            <h2>Globális kategória kedvezmény</h2>
            <div class="pricing-control-row">
                <label for="globalCategorySelect">Kategória</label>
                <select id="globalCategorySelect" class="form-select">
                    @foreach($sortedCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <label for="globalPercentValue">Kedvezmény %</label>
                <input type="number" id="globalPercentValue" class="form-control" min="0" max="100" step="0.01">
                <button id="btnGlobalApply" class="btn btn-danger" type="button">Frissítés mindenkinek</button>
            </div>
        </div>
    </section>

    <section id="controlsWrapper" class="card" hidden>
        <div class="card-body pricing-control-row">
            <label for="productSearch">Keresés</label>
            <input type="search" id="productSearch" class="form-control" placeholder="Név vagy cikkszám">
            <label for="categoryFilter">Kategória</label>
            <select id="categoryFilter" class="form-select"><option value="">Összes kategória</option>@foreach($sortedCategories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select>
            <label><input type="checkbox" id="onlyModifiedFilter"> Csak módosított</label>
        </div>
    </section>

    <section id="tableWrapper" class="table-responsive" hidden>
        <table class="table" id="priceTable"><thead><tr><th>Termék</th><th>Alapár</th><th>Kategória %</th><th>Extra %</th><th>Kalkulált ár</th><th>Fix ár</th><th>Megjelenített ár</th><th>Művelet</th></tr></thead><tbody id="priceTableBody"></tbody></table>
        <nav id="paginationWrapper" aria-label="Árazási lapozás"></nav>
    </section>
    <section id="userTableWrapper" class="table-responsive" hidden>
        <table class="table"><thead><tr><th>Ügyfél</th><th>Email</th><th>Módosított ár</th><th>Fix ár</th></tr></thead><tbody id="userTableBody"></tbody></table>
        <nav id="userPaginationWrapper" aria-label="Ügyfél lapozás"></nav>
    </section>
</main>

<style>
.pricing-header, .pricing-control-row, .pricing-modes { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; }
.pricing-header { justify-content: space-between; } .pricing-header h1, .pricing-global h2 { margin: 0; font-size: 1.25rem; }
.pricing-control-row label { font-weight: 600; } .pricing-control-row .form-control, .pricing-control-row .form-select { max-width: 280px; }
.pricing-global, #controlsWrapper, #tableWrapper, #userTableWrapper { margin-top: 1rem; }
.pricing-global h2 { margin-bottom: 1rem; } .calculated-price { font-weight: 700; color: #b42318; }
@media (max-width: 700px) { .pricing-control-row .form-control, .pricing-control-row .form-select { max-width: none; flex: 1 1 100%; } .pricing-header { align-items: flex-start; flex-direction: column; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const byId = (selector) => document.querySelector(selector);
    const root = document.querySelector('[data-pricing-root]');
    if (!root || !csrf) return;
    let mode = new URLSearchParams(location.search).get('mode') === 'product' ? 'product' : 'user';
    let selectedUser = new URLSearchParams(location.search).get('user') || '';
    let selectedProduct = new URLSearchParams(location.search).get('product') || '';
    let timer;
    const request = async (url, options = {}) => {
        const response = await fetch(url, {credentials: 'same-origin', ...options, headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json', ...(options.headers || {})}});
        if (!response.ok) throw new Error('Az árazási adat betöltése nem sikerült.');
        return response.json();
    };
    const show = (selector, visible) => { const element = byId(selector); if (element) element.hidden = !visible; };
    const stateUrl = (page = 1) => { const url = new URL(location.href); url.searchParams.set('mode', mode); url.searchParams.set(mode === 'user' ? 'user' : 'product', mode === 'user' ? selectedUser : selectedProduct); url.searchParams.set('page', page); history.replaceState({}, '', url); };
    const renderPages = (target, data, load) => { target.innerHTML = ''; for (let page = 1; page <= data.last_page; page += 1) { const link = document.createElement('button'); link.type = 'button'; link.textContent = page; link.className = page === data.current_page ? 'btn btn-primary' : 'btn btn-outline-secondary'; link.addEventListener('click', () => load(page)); target.append(link); } };
    const updateRow = (row) => { const base = Number(row.dataset.basePrice) || 0; const a = Number(row.querySelector('.cat-disc-input')?.value) || 0; const b = Number(row.querySelector('.extra-disc-input')?.value) || 0; const fixed = Number(row.querySelector('.fix-price-input')?.value) || 0; const calculated = Math.round(base * (1 - ((a + b) / 100))); row.querySelector('.calculated-price').textContent = `${calculated} Ft`; row.querySelector('.final-display-price').textContent = `${fixed > 0 ? fixed : calculated} Ft`; };
    const loadMatrix = async (page = 1) => { if (!selectedUser) return; show('#mainLoader', true); stateUrl(page); const data = await request(`/adminpanel/special-prices/matrix-ajax/${selectedUser}?page=${page}&search=${encodeURIComponent(byId('#productSearch')?.value || '')}&category_id=${encodeURIComponent(byId('#categoryFilter')?.value || '')}&only_modified=${byId('#onlyModifiedFilter')?.checked ? 1 : 0}`); const body = byId('#priceTableBody'); body.innerHTML = ''; data.products.data.forEach((product) => { const special = product.special_price || {}; const pricing = product.pricing || {}; const row = document.createElement('tr'); row.dataset.id = product.id; row.dataset.basePrice = product.price; row.dataset.categoryId = product.category_id || ''; row.innerHTML = `<td><strong>${product.title}</strong><br><small>${product.serial_number || '-'}</small></td><td>${product.price} Ft</td><td><input class="form-control cat-disc-input" type="number" value="${pricing.category_discount ?? special.discount_percent ?? 0}"></td><td><input class="form-control extra-disc-input" type="number" value="${pricing.extra_discount ?? special.extra_percent ?? 0}"></td><td class="calculated-price">${pricing.calculated_price ?? product.price} Ft</td><td><input class="form-control fix-price-input" type="number" value="${pricing.fixed_price ?? special.price ?? ''}"></td><td class="final-display-price">${pricing.final_price ?? product.price} Ft</td><td><button class="btn btn-primary btn-save-row" type="button">Mentés</button></td>`; body.append(row); }); renderPages(byId('#paginationWrapper'), data.products, loadMatrix); show('#mainLoader', false); show('#controlsWrapper', true); show('#tableWrapper', true); };
    const loadUsers = async (page = 1) => { if (!selectedProduct) return; show('#mainLoader', true); stateUrl(page); const data = await request(`/adminpanel/special-prices/product-users-ajax/${selectedProduct}?page=${page}&search=${encodeURIComponent(byId('#productSearch')?.value || '')}&only_modified=${byId('#onlyModifiedFilter')?.checked ? 1 : 0}`); const body = byId('#userTableBody'); body.innerHTML = ''; data.users.data.forEach((user) => { const special = user.special_prices?.[0]; const row = document.createElement('tr'); row.innerHTML = `<td>${user.name}</td><td>${user.email}</td><td>${special ? 'Igen' : 'Nem'}</td><td>${special ? `${special.price} Ft` : '-'}</td>`; body.append(row); }); renderPages(byId('#userPaginationWrapper'), data.users, loadUsers); show('#mainLoader', false); show('#userTableWrapper', true); };
    const applyMode = () => { document.querySelectorAll('#modeToggle [data-mode]').forEach((button) => button.classList.toggle('active', button.dataset.mode === mode)); show('#modeUserControls', mode === 'user'); show('#modeProductControls', mode === 'product'); show('#tableWrapper', false); show('#userTableWrapper', false); if (mode === 'user' && selectedUser) loadMatrix(); if (mode === 'product' && selectedProduct) loadUsers(); };
    document.querySelectorAll('#modeToggle [data-mode]').forEach((button) => button.addEventListener('click', () => { mode = button.dataset.mode; applyMode(); }));
    byId('#userSelect').value = selectedUser; byId('#userSelect').addEventListener('change', (event) => { selectedUser = event.target.value; mode = 'user'; applyMode(); });
    byId('#productSelect').value = selectedProduct; byId('#productSelect').addEventListener('change', (event) => { selectedProduct = event.target.value; mode = 'product'; applyMode(); });
    root.addEventListener('input', (event) => { if (event.target.matches('.cat-disc-input, .extra-disc-input, .fix-price-input')) updateRow(event.target.closest('tr')); if (event.target.id === 'productSearch') { clearTimeout(timer); timer = setTimeout(() => mode === 'user' ? loadMatrix() : loadUsers(), 450); } });
    root.addEventListener('change', (event) => { if (event.target.id === 'categoryFilter' || event.target.id === 'onlyModifiedFilter') mode === 'user' ? loadMatrix() : loadUsers(); });
    root.addEventListener('click', async (event) => { const button = event.target.closest('.btn-save-row'); if (!button) return; const row = button.closest('tr'); const data = new URLSearchParams({_token: csrf, user_id: selectedUser, product_id: row.dataset.id, category_id: row.dataset.categoryId, discount_percent: row.querySelector('.cat-disc-input').value, extra_percent: row.querySelector('.extra-disc-input').value, fix_price: row.querySelector('.fix-price-input').value}); button.disabled = true; try { await request('/adminpanel/special-prices/save-matrix', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: data}); row.classList.add('table-success'); setTimeout(() => row.classList.remove('table-success'), 1000); } finally { button.disabled = false; } });
    byId('#btnGlobalApply').addEventListener('click', async () => { const percent = byId('#globalPercentValue').value; if (percent === '' || Number(percent) < 0 || Number(percent) > 100) return alert('Adj meg 0 és 100 közötti értéket.'); if (!confirm('A kategória kedvezménye minden ügyfélnél módosul. Biztosan folytatod?')) return; const button = byId('#btnGlobalApply'); button.disabled = true; try { const response = await request('/adminpanel/special-prices/global-category-update', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({_token: csrf, category_id: byId('#globalCategorySelect').value, discount_percent: percent})}); alert(response.message || 'A módosítás elkészült.'); if (mode === 'user') loadMatrix(); } finally { button.disabled = false; } });
    applyMode();
});
</script>
@endsection
