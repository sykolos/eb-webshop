import Alpine from '@alpinejs/csp';
import '../css/v6.css';
import '../css/home-v6.css';
import './cart';
import './tracking';

window.Alpine = Alpine;
window.ElectroBusinessV6 = true;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('[data-home-nav]');
    const menuButton = document.querySelector('[data-home-menu-toggle]');
    const searchInput = document.querySelector('[data-home-search-input]');
    const suggestions = document.querySelector('#home-v6-search-suggestions');
    const cartToast = document.querySelector('[data-home-cart-toast]');
    const setSuggestionsVisible = (visible) => {
        suggestions?.classList.toggle('is-visible', visible);
        searchInput?.setAttribute('aria-expanded', String(visible));
    };

    menuButton?.addEventListener('click', () => {
        const open = nav?.classList.toggle('is-open') ?? false;
        menuButton.setAttribute('aria-expanded', String(open));
    });

    let debounce;
    let active = -1;
    searchInput?.addEventListener('input', () => {
        window.clearTimeout(debounce);
        const query = searchInput.value.trim();
        if (query.length < 2) {
            suggestions?.replaceChildren();
            setSuggestionsVisible(false);
            return;
        }
        debounce = window.setTimeout(async () => {
            const url = new URL(searchInput.dataset.autocompleteUrl, window.location.origin);
            url.searchParams.set('q', query);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok || !suggestions) {
                setSuggestionsVisible(false);
                return;
            }
            const data = await response.json();
            suggestions.replaceChildren();
            active = -1;
            (data.results || []).slice(0, 6).forEach((result) => {
                const link = document.createElement('a');
                link.href = result.url;
                link.setAttribute('role', 'option');
                link.innerHTML = `<strong>${escapeHtml(result.title)}</strong>${result.serial_number ? `<small>Cikkszám: ${escapeHtml(result.serial_number)}</small>` : ''}`;
                suggestions.append(link);
            });
            setSuggestionsVisible(suggestions.children.length > 0);
        }, 220);
    });

    searchInput?.addEventListener('keydown', (event) => {
        const links = [...(suggestions?.querySelectorAll('a') || [])];
        if (!links.length) return;
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            active = event.key === 'ArrowDown' ? (active + 1) % links.length : (active - 1 + links.length) % links.length;
            links.forEach((link, index) => link.classList.toggle('is-active', index === active));
        }
        if (event.key === 'Enter' && active >= 0) {
            event.preventDefault();
            links[active].click();
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.home-v6-search')) setSuggestionsVisible(false);
    });

    document.querySelectorAll('[data-home-cart-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const response = await fetch(form.action, { method: 'POST', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(form) });
            if (!response.ok) return;
            const data = await response.json();
            document.querySelectorAll('[data-home-cart-count]').forEach((node) => { node.textContent = data.count; });
            if (cartToast) {
                cartToast.textContent = data.message || 'A termék a kosárba került.';
                cartToast.hidden = false;
                window.setTimeout(() => { cartToast.hidden = true; }, 2600);
            }
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.target);
        if (!input) return;
        button.addEventListener('click', () => {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.textContent = visible ? 'Mutatás' : 'Elrejtés';
            button.setAttribute('aria-label', visible ? 'Jelszó megjelenítése' : 'Jelszó elrejtése');
        });
    });
});

function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character]));
}
