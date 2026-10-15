import '../sass/admin.scss';
import '../sass/app.scss';

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('toggle-sidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar || !overlay) return;

    const close = () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    };

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    });
    overlay.addEventListener('click', close);
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));
});
