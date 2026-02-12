document.addEventListener('DOMContentLoaded', function () {
    const layout = document.querySelector('.sciuuus-shop-layout');
    const sidebar = layout ? layout.querySelector('.sciuuus-sidebar') : null;
    const products = layout ? layout.querySelector('.sciuuus-products') : null;

    if (!layout || !sidebar || !products) {
        return;
    }

    const productsHeader = products.querySelector('.woocommerce-products-header');
    const heroHeader = products.querySelector(':scope > .hero-section');
    const headerToPromote = heroHeader || productsHeader;

    if (headerToPromote && headerToPromote.parentElement !== layout) {
        layout.insertBefore(headerToPromote, layout.firstChild);
    }

    const mobileQuery = window.matchMedia('(max-width: 1023px)');

    const existingToggle = layout.querySelector('.sciuuus-filters-toggle');
    const toggleButton = existingToggle || document.createElement('button');
    if (!existingToggle) {
        toggleButton.type = 'button';
        toggleButton.className = 'sciuuus-filters-toggle';
        toggleButton.setAttribute('aria-expanded', 'false');
        toggleButton.setAttribute('aria-controls', 'sciuuus-sidebar');
        toggleButton.textContent = 'Show Filters';
        layout.insertBefore(toggleButton, sidebar);
    }

    const existingOverlay = layout.querySelector('.sciuuus-filters-overlay');
    const overlay = existingOverlay || document.createElement('button');
    if (!existingOverlay) {
        overlay.type = 'button';
        overlay.className = 'sciuuus-filters-overlay';
        overlay.setAttribute('aria-label', 'Close filters panel');
        overlay.setAttribute('tabindex', '-1');
        layout.appendChild(overlay);
    }

    sidebar.id = 'sciuuus-sidebar';

    let closeButton = sidebar.querySelector('.sciuuus-filters-close');
    if (!closeButton) {
        closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'sciuuus-filters-close';
        closeButton.textContent = 'Close Filters';
        sidebar.insertBefore(closeButton, sidebar.firstChild);
    }

    function setOpenState(isOpen) {
        layout.classList.toggle('is-filters-open', isOpen);
        toggleButton.setAttribute('aria-expanded', String(isOpen));

        if (isOpen) {
            sidebar.setAttribute('aria-hidden', 'false');
            closeButton.focus();
        } else {
            sidebar.setAttribute('aria-hidden', 'true');
            toggleButton.focus();
        }
    }

    function closeOnDesktop() {
        if (!mobileQuery.matches) {
            layout.classList.remove('is-filters-open');
            toggleButton.setAttribute('aria-expanded', 'false');
            sidebar.setAttribute('aria-hidden', 'false');
        } else if (!layout.classList.contains('is-filters-open')) {
            sidebar.setAttribute('aria-hidden', 'true');
        }
    }

    toggleButton.addEventListener('click', function () {
        const isOpen = !layout.classList.contains('is-filters-open');
        setOpenState(isOpen);
    });

    overlay.addEventListener('click', function () {
        setOpenState(false);
    });

    closeButton.addEventListener('click', function () {
        setOpenState(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && layout.classList.contains('is-filters-open')) {
            setOpenState(false);
        }
    });

    mobileQuery.addEventListener('change', closeOnDesktop);
    closeOnDesktop();
});
