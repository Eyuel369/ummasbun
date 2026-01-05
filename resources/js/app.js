import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const loadingOverlay = document.getElementById('app-loading');
const loadingBody = document.body;

const showLoadingOverlay = () => {
    if (!loadingBody) {
        return;
    }
    loadingBody.classList.add('is-loading');
    if (loadingOverlay) {
        loadingOverlay.setAttribute('aria-hidden', 'false');
    }
};

const hideLoadingOverlay = () => {
    if (!loadingBody) {
        return;
    }
    loadingBody.classList.remove('is-loading');
    if (loadingOverlay) {
        loadingOverlay.setAttribute('aria-hidden', 'true');
    }
};

document.addEventListener('click', (event) => {
    if (event.defaultPrevented) {
        return;
    }
    const link = event.target.closest('a');
    if (!link || link.dataset.noLoading !== undefined) {
        return;
    }
    if (link.target && link.target !== '_self') {
        return;
    }
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
        return;
    }

    const nextUrl = new URL(href, window.location.origin);
    if (nextUrl.origin !== window.location.origin) {
        return;
    }
    if (nextUrl.pathname === window.location.pathname && nextUrl.search === window.location.search) {
        return;
    }

    showLoadingOverlay();
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }
    if (form.dataset.noLoading !== undefined) {
        return;
    }
    if (form.dataset.confirm !== undefined && form.dataset.confirmApproved !== 'true') {
        return;
    }

    showLoadingOverlay();
});

window.addEventListener('pageshow', hideLoadingOverlay);
