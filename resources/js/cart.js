document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const cartRoot = document.getElementById('cart-icon-container');
    const cartBody = document.getElementById('cart-body');
    if (!csrf || !cartRoot) return;

    const routes = {
        update: cartRoot.dataset.updateUrl || '/cart/update',
        remove: cartRoot.dataset.removeUrl || '/cart/remove',
    };

    const setCount = (count) => {
        document.querySelectorAll('.cart-badge, #cart-count').forEach((badge) => {
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
