document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const cartRoot = document.getElementById('cart-icon-container');
    const cartBody = document.getElementById('cart-body');
    if (!csrf || !cartRoot) return;

    const routes = {
        update: cartRoot.dataset.updateUrl || '/cart/update',
        remove: cartRoot.dataset.removeUrl || '/cart/remove',
        preview: cartRoot.dataset.previewUrl || '/cart/preview',
    };

    const setCount = (count) => {
        document.querySelectorAll('.cart-badge, #cart-count, [data-home-cart-count]').forEach((badge) => {
            badge.textContent = String(count);
        });
    };

    const request = async (url, options = {}) => {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
                ...(options.headers || {}),
            },
        });
        if (!response.ok) throw new Error('Nem sikerült frissíteni a kosarat.');
        return response.json();
    };

    const preview = document.getElementById('home-v6-cart-preview');
    let hideTimer;
    const hidePreview = () => {
        if (!preview) return;
        preview.hidden = true;
        cartRoot.setAttribute('aria-expanded', 'false');
    };
    const showPreview = async () => {
        if (!preview) return;
        window.clearTimeout(hideTimer);
        preview.hidden = false;
        cartRoot.setAttribute('aria-expanded', 'true');
        preview.innerHTML = '<div class="home-v6-cart-preview__loading" role="status">Kosár betöltése…</div>';
        try {
            const response = await fetch(routes.preview, {headers: {Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error();
            preview.innerHTML = await response.text();
        } catch {
            preview.innerHTML = '<div class="home-v6-cart-preview__empty">A kosár előnézete most nem érhető el.</div>';
        }
    };
    const scheduleHide = () => {
        window.clearTimeout(hideTimer);
        hideTimer = window.setTimeout(hidePreview, 280);
    };

    if (preview) {
        cartRoot.addEventListener('mouseenter', showPreview);
        cartRoot.addEventListener('mouseleave', scheduleHide);
        preview.addEventListener('mouseenter', () => window.clearTimeout(hideTimer));
        preview.addEventListener('mouseleave', scheduleHide);
        document.addEventListener('click', (event) => {
            if (!cartRoot.contains(event.target) && !preview.contains(event.target)) hidePreview();
        });
        preview.addEventListener('click', async (event) => {
            const button = event.target.closest('.btn-increase, .btn-decrease, .btn-remove');
            if (!button) return;
            event.preventDefault();
            const remove = button.classList.contains('btn-remove');
            const body = new URLSearchParams({key: button.dataset.key || ''});
            if (!remove) body.set('change', button.classList.contains('btn-increase') ? '1' : '-1');
            try {
                await request(remove ? routes.remove : routes.update, {
                    method: remove ? 'DELETE' : 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
                    body,
                });
                await Promise.all([showPreview(), fetch(routes.count, {headers: {Accept: 'application/json'}}).then((response) => response.json()).then((data) => setCount(data.count))]);
            } catch {
                preview.innerHTML = '<div class="home-v6-cart-preview__empty">A kosár frissítése nem sikerült.</div>';
            }
        });
    }

    window.showCartPreview = showPreview;

    const setBusy = (busy) => {
        document.querySelectorAll('.quantity-increase, .quantity-decrease, .cart-remove').forEach((button) => {
            button.disabled = busy;
        });
        const spinner = document.getElementById('ajax-spinner');
        if (spinner) spinner.style.display = busy ? 'block' : 'none';
    };

    document.querySelectorAll('.quantity-increase, .quantity-decrease').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();
            setBusy(true);
            try {
                const data = await request(routes.update, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        key: button.dataset.key,
                        change: Number(button.dataset.change),
                    }),
                });
                setCount(data.count);
                const row = button.closest('.cart-row');
                if (row && data.line) {
                    row.querySelector('.cart-quantity').textContent = data.line.quantity;
                    const subtotal = row.querySelector('.cart-subtotal');
                    if (subtotal && data.line.line_net !== null) {
                        subtotal.textContent = `${Number(data.line.line_net).toLocaleString('hu-HU')} Ft`;
                    }
                }
                if (data.summary) {
                    const total = document.querySelector('.cart-total strong');
                    if (total) total.textContent = `${Number(data.summary.net).toLocaleString('hu-HU')} Ft`;
                }
            } catch (error) {
                window.alert(error.message);
            } finally {
                setBusy(false);
            }
        });
    });

    document.querySelectorAll('.cart-remove').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();
            setBusy(true);
            try {
                const data = await request(routes.remove, {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({key: button.dataset.key}),
                });
                setCount(data.count);
                button.closest('.cart-row')?.remove();
                if (cartBody && !cartBody.querySelector('.cart-row')) {
                    window.location.reload();
                }
            } catch (error) {
                window.alert(error.message);
            } finally {
                setBusy(false);
            }
        });
    });
});
