@extends('layouts.admin')
@section('title', 'Kiemelt termékek')
@section('content')
<main class="container" data-recommended-root>
    <h1>Kiemelt termékek</h1>
    <section class="card"><div class="card-header bg-dark text-white"><h2>Aktuális sorrend</h2></div><div class="table-responsive"><table class="table" id="recommended-live-table"><thead><tr><th>#</th><th>Cikkszám</th><th>Megnevezés</th><th>Művelet</th></tr></thead><tbody></tbody></table></div></section>
    <form method="POST" action="{{ route('adminpanel.recommended.update') }}" id="recommended-form">
        @csrf
        <section class="card"><div class="card-header bg-dark text-white"><div class="d-flex justify-content-between align-items-center"><h2>Terméklista</h2><input type="search" id="product-search" class="form-control" placeholder="Keresés..."></div></div><div class="card-body" id="product-table">@include('admin.pages.recommended.table', ['products' => $products, 'recommendedIds' => $recommendedIds])</div></section>
        <button type="submit" class="btn btn-primary">Mentés</button><div id="hidden-selected-products"></div>
    </form>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-recommended-root]');
    if (!root) return;
    let selected = new Set(@json($recommendedIds).map(String));
    const data = @json($recommendedData);
    const table = document.querySelector('#recommended-live-table tbody');
    const updateUrl = () => { const url = new URL(location.href); const query = document.querySelector('#product-search').value; query ? url.searchParams.set('search', query) : url.searchParams.delete('search'); selected.size ? url.searchParams.set('selected', [...selected].join(',')) : url.searchParams.delete('selected'); history.replaceState({}, '', url); };
    const syncChecks = () => document.querySelectorAll('.product-checkbox').forEach((box) => { box.checked = selected.has(box.value); });
    const render = () => { table.replaceChildren(); const ids = [...selected]; if (!ids.length) { table.innerHTML = '<tr><td colspan="4">Nincs jelenleg kiemelt termék.</td></tr>'; return; } ids.forEach((id, index) => { const record = data[id] || {}; const row = document.createElement('tr'); row.innerHTML = `<td>${index + 1}</td><td>${record.serial_number || id}</td><td>${record.title || 'Termék'}</td><td><button class="btn btn-outline-secondary" type="button" data-move="-1" data-id="${id}" ${index === 0 ? 'disabled' : ''}>Fel</button> <button class="btn btn-outline-secondary" type="button" data-move="1" data-id="${id}" ${index === ids.length - 1 ? 'disabled' : ''}>Le</button> <button class="btn btn-outline-danger" type="button" data-remove="${id}">Eltávolítás</button></td>`; table.append(row); }); };
    root.addEventListener('change', (event) => { if (!event.target.matches('.product-checkbox')) return; event.target.checked ? selected.add(event.target.value) : selected.delete(event.target.value); syncChecks(); render(); updateUrl(); });
    table.addEventListener('click', (event) => { const move = event.target.closest('[data-move]'); const remove = event.target.closest('[data-remove]'); const ids = [...selected]; if (remove) selected.delete(remove.dataset.remove); if (move) { const index = ids.indexOf(move.dataset.id); const target = index + Number(move.dataset.move); if (target >= 0 && target < ids.length) [ids[index], ids[target]] = [ids[target], ids[index]]; selected = new Set(ids); } syncChecks(); render(); updateUrl(); });
    let searchTimer; document.querySelector('#product-search').addEventListener('input', (event) => { updateUrl(); clearTimeout(searchTimer); searchTimer = setTimeout(async () => { const url = new URL('/adminpanel/recommended/ajax', location.origin); url.searchParams.set('q', event.target.value); const response = await fetch(url, {headers: {Accept: 'application/json'}}); const result = await response.json(); document.querySelector('#product-table').innerHTML = result.html || ''; syncChecks(); }, 300); });
    document.querySelector('#recommended-form').addEventListener('submit', () => { const target = document.querySelector('#hidden-selected-products'); target.replaceChildren(); (selected.size ? [...selected] : ['__empty__']).forEach((id) => { const input = document.createElement('input'); input.type = 'hidden'; input.name = 'products[]'; input.value = id; target.append(input); }); });
    syncChecks(); render(); updateUrl();
});
</script>
@endsection
