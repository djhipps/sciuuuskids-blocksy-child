document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sciuuus-sidebar');
    const header  = sidebar.querySelector('.wc-block-product-filters__header');

    if (!header) return;

    header.addEventListener('click', function() {
        sidebar.classList.toggle('is-open');
    });
});
