/* Unified Dashboard Interactions, Theme Mode, Notifications & Live SVG Charts */
document.addEventListener('DOMContentLoaded', () => {
    // Dark / Light mode persistence
    const savedTheme = localStorage.getItem('dash_theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    const themeBtn = document.querySelector('[data-theme-toggle]');
    themeBtn?.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('dash_theme', next);
    });

    // Responsive Sidebar Drawer
    const sidebar = document.querySelector('.dash-sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
        backdrop?.classList.toggle('open');
    });
    backdrop?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('open');
    });

    // Dropdown toggles (Notifications / Profile)
    document.querySelectorAll('[data-dropdown-btn]').forEach((btn) => {
        btn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            const targetId = btn.getAttribute('data-dropdown-btn');
            const panel = document.getElementById(targetId);
            document.querySelectorAll('.dropdown-menu-panel.open').forEach((p) => {
                if (p !== panel) p.classList.remove('open');
            });
            panel?.classList.toggle('open');
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu-panel.open').forEach((p) => p.classList.remove('open'));
    });
    document.querySelectorAll('.dropdown-menu-panel').forEach((p) => {
        p.addEventListener('click', (ev) => ev.stopPropagation());
    });

    // Confirm action dialogs
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (ev) => {
            const msg = el.getAttribute('data-confirm') || 'Are you sure you want to continue?';
            if (!window.confirm(msg)) {
                ev.preventDefault();
            }
        });
    });

    // AJAX Mark Notification Read
    document.querySelectorAll('[data-notif-read]').forEach((item) => {
        item.addEventListener('click', async () => {
            const endpoint = item.getAttribute('data-notif-read');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            if (!endpoint) return;
            try {
                await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new URLSearchParams({ _csrf_token: csrf }),
                });
                item.style.opacity = '0.6';
            } catch {}
        });
    });

    // Live Image Upload Preview (Branding & Profile & Packages)
    document.querySelectorAll('[data-image-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const targetSelector = input.getAttribute('data-image-preview');
            const img = document.querySelector(targetSelector);
            if (img && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { img.src = e.target.result; };
                reader.readAsDataURL(input.files[0]);
            }
        });
    });

    // Live Color Picker Sync (Website Branding)
    document.querySelectorAll('[data-color-sync]').forEach((input) => {
        input.addEventListener('input', () => {
            const varName = input.getAttribute('data-color-sync');
            if (varName) {
                document.documentElement.style.setProperty(varName, input.value);
            }
            const label = document.querySelector(`[data-color-hex="${input.name}"]`);
            if (label) label.textContent = input.value.toUpperCase();
        });
    });
});
