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
