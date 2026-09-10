(() => {
    'use strict';

    const form = document.getElementById('adminForm');

    if (!form || !window.Joomla) {
        return;
    }

    form.addEventListener('change', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const field = target.closest('[data-jsm-mark-row]');

        if (!field || !form.contains(field) || field.hasAttribute('disabled')) {
            return;
        }

        const row = field.dataset.jsmMarkRow || '';

        if (!/^\d+$/.test(row)) {
            return;
        }

        const checkbox = document.getElementById(`cb${row}`);

        if (!(checkbox instanceof HTMLInputElement) || checkbox.checked) {
            return;
        }

        checkbox.checked = true;

        if (typeof Joomla.isChecked === 'function') {
            Joomla.isChecked(true);
        }
    });
})();
