(() => {
    'use strict';

    const form = document.querySelector('[data-jsm-allprojects-form]');

    if (!form) {
        return;
    }

    const search = form.querySelector('#filter_search');
    const order = form.elements.namedItem('filter_order');
    const direction = form.elements.namedItem('filter_order_Dir');

    const submitOrdering = (column, dir) => {
        if (!order || !direction) {
            return;
        }

        order.value = column;
        direction.value = dir;
        form.submit();
    };

    window.tableOrdering = submitOrdering;

    if (window.Joomla && typeof Joomla.tableOrdering !== 'function') {
        Joomla.tableOrdering = submitOrdering;
    }

    form.addEventListener('change', (event) => {
        const trigger = event.target.closest('[data-jsm-auto-submit]');

        if (trigger && form.contains(trigger)) {
            form.submit();
        }
    });

    form.addEventListener('click', (event) => {
        const clear = event.target.closest('[data-jsm-clear-filter]');

        if (!clear || !form.contains(clear)) {
            return;
        }

        if (search) {
            search.value = '';
        }

        form.submit();
    });
})();
