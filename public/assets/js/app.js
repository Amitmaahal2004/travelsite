/* Frontend Interactive Behaviors */
document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.site-header');
    if (header && !header.classList.contains('header-solid')) {
        const onScroll = () => {
            header.classList.toggle('is-scrolled', window.scrollY > 40);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Mobile drawer
    const menuBtn = document.querySelector('[data-mobile-open]');
    const drawer = document.querySelector('[data-mobile-drawer]');
    const closeBtn = document.querySelector('[data-mobile-close]');
    if (menuBtn && drawer) {
        menuBtn.addEventListener('click', () => drawer.classList.add('open'));
        closeBtn?.addEventListener('click', () => drawer.classList.remove('open'));
        drawer.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => drawer.classList.remove('open')));
    }

    // Intersection Observer Reveal Animations
    const reveals = document.querySelectorAll('.reveal');
    if (reveals.length && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        reveals.forEach((el) => io.observe(el));
    } else {
        reveals.forEach((el) => el.classList.add('is-visible'));
    }

    // Promo code copy buttons
    document.querySelectorAll('[data-copy-code]').forEach((el) => {
        el.addEventListener('click', () => {
            const code = el.getAttribute('data-copy-code');
            if (!code) return;
            navigator.clipboard?.writeText(code).catch(() => {});
            showToast(`Offer code ${code} copied to clipboard`);
        });
    });

    // AJAX Wishlist Toggle
    document.querySelectorAll('[data-wishlist-btn]').forEach((btn) => {
        btn.addEventListener('click', async (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const type = btn.getAttribute('data-item-type') || 'package';
            const id = btn.getAttribute('data-item-id');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const endpoint = btn.getAttribute('data-endpoint');
            if (!endpoint || !id) return;

            btn.disabled = true;
            try {
                const body = new URLSearchParams({ item_type: type, item_id: id, _csrf_token: csrf });
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body,
                });
                const data = await res.json();
                if (res.status === 401 && data.redirect) {
                    showToast('Please sign in as a customer to save to your wishlist.');
                    setTimeout(() => { window.location.href = data.redirect; }, 900);
                    return;
                }
                if (data.success) {
                    btn.classList.toggle('active', !!data.saved);
                    showToast(data.message);
                } else {
                    showToast(data.message || 'Could not update wishlist.');
                }
            } catch (err) {
                showToast('Network error while updating wishlist.');
            } finally {
                btn.disabled = false;
            }
        });
    });

    // AJAX Newsletter Form
    const newsForm = document.querySelector('[data-newsletter-form]');
    if (newsForm) {
        newsForm.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const submitBtn = newsForm.querySelector('button[type="submit"]');
            const msgEl = document.querySelector('[data-newsletter-msg]');
            const origText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Subscribing...';

            try {
                const res = await fetch(newsForm.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(newsForm),
                });
                const data = await res.json();
                if (msgEl) {
                    msgEl.textContent = data.message || '';
                    msgEl.style.color = data.success ? 'var(--status-success)' : 'var(--status-danger)';
                }
                if (data.success) {
                    newsForm.reset();
                    showToast(data.message);
                }
            } catch {
                if (msgEl) msgEl.textContent = 'Unable to subscribe right now.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = origText;
            }
        });
    }

    // FAQ Accordion
    document.querySelectorAll('[data-faq-trigger]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const panel = btn.nextElementSibling;
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            if (panel) panel.style.display = isOpen ? 'none' : 'block';
        });
    });
});

window.showToast = function (message) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast-item';
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
};
