<section class="shop-v6-list-view" aria-label="Lista nézet">
    <div class="shop-v6-list-intro">
        <span class="v6-eyebrow">LISTA NÉZET</span>
        <h2>Termékek áttekinthető listában.</h2>
        <p>Keressen cikkszám vagy terméknév alapján, adja meg a szükséges mennyiséget, és tegye a terméket közvetlenül a kosárba.</p>
    </div>
    <div class="shop-v6-list-filters">
        <label for="quick-search">Keresés<input type="search" id="quick-search" placeholder="Cikkszám vagy terméknév"></label>
        <label for="quick-category">Kategória<select id="quick-category">
            <option value="">Összes kategória</option>
            <option value="latest">Új termékek</option>
            <option value="highlighted">Kiemelt termékek</option>
            @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ str_repeat('— ', min((int) ($cat->tree_level ?? 0), 5)) }}{{ $cat->name }}</option>@endforeach
        </select></label>
    </div>
    <div id="quick-spinner" class="shop-v6-list-loading" hidden aria-live="polite">Terméklista betöltése…</div>
    <div id="quick-info" class="shop-v6-list-info"></div>
    <div id="quick-cart-status" class="shop-v6-alert" role="status" aria-live="polite" hidden></div>
    <div id="quick-products-wrapper" class="shop-v6-list-items"></div>
</section>
<script>
const quickSearch = document.getElementById('quick-search');
const quickCategory = document.getElementById('quick-category');
const quickProducts = document.getElementById('quick-products-wrapper');
const quickSpinner = document.getElementById('quick-spinner');
const quickInfo = document.getElementById('quick-info');
const quickStatus = document.getElementById('quick-cart-status');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
const updateListCardPrice = (card) => {
    const input = card?.querySelector('.quick-quantity');
    const output = card?.querySelector('.calculated-price');
    if (!input || !output) return;
    const quantity = Math.max(1, Number.parseInt(input.value, 10) || 1);
    const price = Number(card.dataset.price || 0);
    const multiplier = Number(card.dataset.multiplier || 1);
    output.textContent = (quantity * multiplier * price).toLocaleString('hu-HU') + ' Ft';
};
const loadQuickProducts = async (url = "{{ route('shop.quick.products') }}") => {
    quickSpinner?.removeAttribute('hidden');
    const endpoint = new URL(url, window.location.origin);
    if (endpoint.pathname.endsWith('/quick/products')) {
        endpoint.searchParams.set('search', quickSearch?.value || '');
        endpoint.searchParams.set('category', quickCategory?.value || '');
    }
    try {
        const response = await fetch(endpoint, {headers: {Accept: 'application/json'}});
        if (!response.ok) throw new Error();
        const data = await response.json();
        if (quickProducts) quickProducts.innerHTML = data.html || '';
        if (quickInfo) quickInfo.textContent = data.info || '';
        quickProducts?.querySelectorAll('.quick-list-item').forEach(updateListCardPrice);
    } finally {
        quickSpinner?.setAttribute('hidden', '');
    }
};
let quickTimeout;
[quickSearch, quickCategory].forEach((field) => field?.addEventListener(field === quickCategory ? 'change' : 'input', () => {
    clearTimeout(quickTimeout);
    quickTimeout = setTimeout(() => loadQuickProducts(), 300);
}));
quickProducts?.addEventListener('input', (event) => {
    const card = event.target.closest('.quick-list-item');
    if (card && event.target.matches('.quick-quantity')) updateListCardPrice(card);
});
quickProducts?.addEventListener('click', async (event) => {
    const pageLink = event.target.closest('.shop-v6-pagination a');
    const button = event.target.closest('.add-to-cart');
    if (pageLink) { event.preventDefault(); await loadQuickProducts(pageLink.href); return; }
    if (!button) return;
    event.preventDefault();
    const card = button.closest('.quick-list-item');
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Mentés…';
    try {
        const response = await fetch(button.dataset.url, {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'X-CSRF-TOKEN': csrf, Accept: 'application/json'}, body: new URLSearchParams({_token: csrf || '', quantity: card?.querySelector('.quick-quantity')?.value || '1'})});
        if (!response.ok) throw new Error();
        if (quickStatus) { quickStatus.textContent = 'A termék a kosárba került.'; quickStatus.hidden = false; }
        window.showCartPreview?.();
        window.updateCartCount?.();
    } catch {
        if (quickStatus) { quickStatus.textContent = 'Hiba történt a kosárba helyezés során.'; quickStatus.hidden = false; }
    } finally {
        window.setTimeout(() => { button.disabled = false; button.textContent = originalText; }, 1200);
    }
});
loadQuickProducts().catch(() => { if (quickInfo) quickInfo.textContent = 'A terméklista nem tölthető be.'; });
</script>
