// interactions.js — modern vanilla JS for UI polish.
// Coexists with the legacy validation.js (this file uses let/const/arrow fns).
// Note: AJAX endpoints still use XMLHttpRequest in their own scripts to meet
// the assignment requirement (final_project.md line 88).

(() => {
    'use strict';

    // ---------- Toast system ----------
    const ensureToastHost = () => {
        let host = document.getElementById('toastHost');
        if (!host) {
            host = document.createElement('div');
            host.id = 'toastHost';
            host.className = 'toast-host';
            document.body.appendChild(host);
        }
        return host;
    };

    window.showToast = (message, type = 'info', timeout = 3200) => {
        const host = ensureToastHost();
        const el = document.createElement('div');
        el.className = `toast toast-${type}`;
        el.textContent = message;
        el.style.display = 'block';
        host.appendChild(el);

        const remove = () => {
            el.classList.add('is-leaving');
            setTimeout(() => el.remove(), 260);
        };
        const handle = setTimeout(remove, timeout);
        el.addEventListener('click', () => { clearTimeout(handle); remove(); });
    };

    // Promote server-rendered flash message into a toast
    document.addEventListener('DOMContentLoaded', () => {
        const server = document.getElementById('serverToast');
        if (server) {
            const msg = server.getAttribute('data-msg') || server.textContent.trim();
            const type = server.getAttribute('data-type') || 'info';
            if (msg) window.showToast(msg, type);
            server.remove();
        }
    });

    // ---------- Mobile nav toggle ----------
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('navToggle');
        const links = document.getElementById('navLinks');
        if (toggle && links) {
            toggle.addEventListener('click', () => {
                links.classList.toggle('is-open');
            });
            // Close on link click (mobile)
            links.querySelectorAll('a').forEach(a => {
                a.addEventListener('click', () => {
                    if (window.innerWidth <= 880) links.classList.remove('is-open');
                });
            });
        }
    });

    // ---------- Smooth image fallback on broken images ----------
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.product-card img, .product-detail img').forEach(img => {
            img.addEventListener('error', () => {
                const wrap = img.closest('.img-wrap');
                if (wrap) {
                    wrap.innerHTML =
                        '<div class="img-fallback">' +
                        '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                        '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>' +
                        '<polyline points="3.27 6.96 12 12.01 20.73 6.96"/>' +
                        '<line x1="12" y1="22.08" x2="12" y2="12"/>' +
                        '</svg><span>No image</span></div>';
                }
            });
        });
    });

    // ---------- Highlight active nav link ----------
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const c = params.get('c') || 'auth';
        const a = params.get('a') || 'login';
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href') || '';
            if (href.includes('c=' + c) && href.includes('a=' + a)) {
                link.classList.add('active');
            }
        });
    });

    // ---------- Confirm before destructive actions ----------
    document.addEventListener('click', (e) => {
        const el = e.target.closest('[data-confirm]');
        if (el) {
            const msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(msg)) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    });
})();
