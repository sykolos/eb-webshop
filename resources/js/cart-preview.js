const cartRoot = document.getElementById('cart-icon-container');
const preview = document.getElementById('cart-preview');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

if (cartRoot && preview && csrf) {
    let hideTimer;
    const routes = {
        count: cartRoot.dataset.countUrl || '/cart/count',
        preview: cartRoot.dataset.previewUrl || '/cart/preview',
        update: cartRoot.dataset.updateUrl || '/cart/update',
        remove: cartRoot.dataset.removeUrl || '/cart/remove',
    };

    const request = async (url, options = {}) => {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                Accept: 'application/json, text/html',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
                ...(options.headers || {}),
            },
        });
        if (!response.ok) throw new Error('A kosár frissítése nem sikerült.');
        return response;
    };

    const loading = () => {
        preview.innerHTML = '<div class="cart-preview-loading" role="status">Betöltés...</div>';
        preview.style.display = 'block';
    };

    const reload = async () => {
        const response = await request(routes.preview);
        preview.innerHTML = await response.text();
    };

    const updateCount = async () => {
        const response = await request(routes.count);
        const data = await response.json();
        document.querySelectorAll('.cart-badge, #cart-count').forEach((badge) => { badge.textContent = String(data.count); });
    };

    const scheduleHide = () => {
        window.clearTimeout(hideTimer);
        hideTimer = window.setTimeout(() => { preview.style.display = 'none'; }, 300);
    };

    if (window.innerWidth > 768) {
        cartRoot.addEventListener('mouseenter', async () => {
            window.clearTimeout(hideTimer);
            loading();
            try { await reload(); } catch { preview.textContent = 'A kosár előnézete jelenleg nem érhető el.'; }
        });
        cartRoot.addEventListener('mouseleave', scheduleHide);
        preview.addEventListener('mouseenter', () => window.clearTimeout(hideTimer));
        preview.addEventListener('mouseleave', scheduleHide);
    }

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('.btn-increase, .btn-decrease, .btn-remove');
        if (!button || (!cartRoot.contains(button) && !preview.contains(button))) return;
        event.preventDefault();
        loading();
        const remove = button.classList.contains('btn-remove');
        const body = new URLSearchParams({key: button.dataset.key || ''});
        if (!remove) body.set('change', button.classList.contains('btn-increase') ? '1' : '-1');
        try {
            await request(remove ? routes.remove : routes.update, {
                method: remove ? 'DELETE' : 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
                body,
            });
            await Promise.all([reload(), updateCount()]);
        } catch (error) {
            preview.textContent = error.message;
        }
    });

    window.updateCartCount = updateCount;
}
