// 1. jQuery – csak ha szükséges a kosár preview-hoz
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import $ from 'jquery';
window.$ = $;
window.jQuery = $;

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
});

// 2. Bootstrap (ez NEM jQuery-t használ, csak tiszta JS)
import 'bootstrap';

// 3. Axios beállítás
import axios from 'axios';
window.axios = axios;

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// globális libek
import _ from 'lodash';
window._ = _;

// Saját JS
import './cart-preview';
import './cart';

// Stílusok
import '../sass/app.scss';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.dropdown-toggle').forEach((el) => {
        new bootstrap.Dropdown(el);
    });
});

document.querySelectorAll('.dropdown-toggle').forEach((el) => {
    el.addEventListener('click', function (e) {
        e.preventDefault(); // ha kell
        bootstrap.Dropdown.getOrCreateInstance(el).toggle();
    });
});

function closePopup() {
    const overlay = document.getElementById('popupOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('popupOverlay');
    const closeBtn = document.getElementById('closePopup');

    if (closeBtn) {
        closeBtn.addEventListener('click', closePopup);
    }

    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closePopup();
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('global-search');
    const suggestions = document.getElementById('global-search-suggestions');

    if (!input || !suggestions) {
        return;
    }

    let debounceTimer;
    let requestSequence = 0;
    let abortController;
    let activeIndex = -1;

    const closeSuggestions = () => {
        suggestions.replaceChildren();
        suggestions.classList.remove('is-visible');
        input.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
    };

    const renderSuggestions = (results) => {
        suggestions.replaceChildren();
        activeIndex = -1;

        results.forEach((result, index) => {
            const link = document.createElement('a');
            link.href = result.url;
            link.setAttribute('role', 'option');
            link.dataset.index = String(index);

            const title = document.createElement('span');
            title.className = 'search-suggestion-title';
            title.textContent = result.title;
            link.append(title);

            if (result.serial_number) {
                const meta = document.createElement('span');
                meta.className = 'search-suggestion-meta';
                meta.textContent = `Cikkszám: ${result.serial_number}`;
                link.append(meta);
            }

            suggestions.append(link);
        });

        if (results.length) {
            suggestions.classList.add('is-visible');
            input.setAttribute('aria-expanded', 'true');
        } else {
            closeSuggestions();
        }
    };

    const updateActiveSuggestion = (index) => {
        const links = [...suggestions.querySelectorAll('a')];
        links.forEach((link, linkIndex) => link.classList.toggle('is-active', linkIndex === index));
        activeIndex = index;
    };

    input.addEventListener('input', () => {
        window.clearTimeout(debounceTimer);
        const query = input.value.trim();
        if (query.length < 2) {
            closeSuggestions();
            return;
        }

        debounceTimer = window.setTimeout(async () => {
            const sequence = ++requestSequence;
            abortController?.abort();
            abortController = new AbortController();

            try {
                const url = new URL(input.dataset.autocompleteUrl, window.location.origin);
                url.searchParams.set('q', query);
                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: abortController.signal });
                if (!response.ok || sequence !== requestSequence) return;
                const data = await response.json();
                if (sequence === requestSequence) renderSuggestions(Array.isArray(data.results) ? data.results : []);
            } catch (error) {
                if (error.name !== 'AbortError') closeSuggestions();
            }
        }, 220);
    });

    input.addEventListener('keydown', (event) => {
        const links = [...suggestions.querySelectorAll('a')];
        if (!links.length || !suggestions.classList.contains('is-visible')) {
            if (event.key === 'Escape') closeSuggestions();
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            updateActiveSuggestion(activeIndex < links.length - 1 ? activeIndex + 1 : 0);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            updateActiveSuggestion(activeIndex > 0 ? activeIndex - 1 : links.length - 1);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            links[activeIndex].click();
        } else if (event.key === 'Escape') {
            closeSuggestions();
        }
    });

    document.addEventListener('click', (event) => {
        if (!input.closest('.site-search')?.contains(event.target)) closeSuggestions();
    });
});
