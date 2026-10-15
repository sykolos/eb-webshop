<section class="col-12 quick-order-page" aria-label="Gyorsrendelés">
<div class="quick-order-intro mb-4">
    <p class="eyebrow mb-2">Gyorsrendelés</p>
    <h2 class="h3 mb-2">Cikkszám alapján gyorsabban</h2>
    <p class="text-muted mb-0">Keressen egy vagy több terméket, adja meg a csomagszámot, majd tegye a kosárba.</p>
</div>
<div class="card quick-search-panel p-4 mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-6">
            <label for="quick-search" class="form-label fw-bold text-white">Keresés</label>
            <input type="text" id="quick-search" class="form-control" placeholder="Pl. kábelcsatorna...">
        </div>

        <div class="col-md-6">
            <label for="quick-category" class="form-label fw-bold text-white">Kategória</label>
            <select id="quick-category" class="form-select">
                <option value="">Összes kategória</option>
                <option value="latest"> Új termékeink</option>
                <option value="highlighted"> Kiemelt termékeink</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ str_repeat('— ', min((int) ($cat->tree_level ?? 0), 5)) }}{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div id="quick-spinner" class="text-center mt-4 d-none">
    <div class="spinner-border text-dark" role="status">
        <span class="visually-hidden">Betöltés...</span>
    </div>
</div>
<div id="quick-info" class="mb-3 text-muted small"></div>
<div id="quick-cart-status" class="small text-success mb-3" role="status" aria-live="polite"></div>
<div id="quick-products-wrapper" class="d-grid gap-3"></div>


</section>
<script>
const quickSearch = document.getElementById('quick-search');
const quickCategory = document.getElementById('quick-category');
const quickProducts = document.getElementById('quick-products-wrapper');
const quickSpinner = document.getElementById('quick-spinner');
const quickInfo = document.getElementById('quick-info');
const quickStatus = document.getElementById('quick-cart-status');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

const loadQuickProducts = async (url = "{{ route('shop.quick.products') }}") => {
    quickSpinner?.classList.remove('d-none');
    const endpoint = new URL(url, window.location.origin);
    if (endpoint.pathname.endsWith('/quick/products')) {
        endpoint.searchParams.set('search', quickSearch?.value || '');
        endpoint.searchParams.set('category', quickCategory?.value || '');
    }
    try {
        const response = await fetch(endpoint, {headers: {Accept: 'application/json'}});
        const data = await response.json();
        if (quickProducts) quickProducts.innerHTML = data.html || '';
        if (quickInfo) quickInfo.textContent = data.info || '';
    } finally {
        quickSpinner?.classList.add('d-none');
    }
};

let quickTimeout;
[quickSearch, quickCategory].forEach((field) => field?.addEventListener('input', () => {
    clearTimeout(quickTimeout);
    quickTimeout = setTimeout(() => loadQuickProducts(), 400);
}));

quickProducts?.addEventListener('click', async (event) => {
    const pageLink = event.target.closest('.pagination a');
    const button = event.target.closest('.add-to-cart');
    if (pageLink) {
        event.preventDefault();
        await loadQuickProducts(pageLink.href);
        return;
    }
    if (!button) return;
    event.preventDefault();
    const card = button.closest('.card');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.textContent = 'Mentés...';
    try {
        const response = await fetch(button.dataset.url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'X-CSRF-TOKEN': csrf, Accept: 'application/json'},
            body: new URLSearchParams({_token: csrf || '', quantity: card?.querySelector('.quick-quantity')?.value || '1'}),
        });
        if (!response.ok) throw new Error();
        if (quickStatus) quickStatus.textContent = 'A termék a kosárba került.';
        window.updateCartCount?.();
    } catch {
        if (quickStatus) quickStatus.textContent = 'Hiba történt a kosárba helyezés során.';
    } finally {
        setTimeout(() => { button.disabled = false; button.innerHTML = originalText; }, 1500);
    }
});

loadQuickProducts().catch(() => { if (quickInfo) quickInfo.textContent = 'A terméklista nem tölthető be.'; });
</script>
