(() => {
    'use strict';

    const form = document.getElementById('adminForm');

    if (!form) {
        return;
    }

    form.addEventListener('change', (event) => {
        const field = event.target.closest('[data-jsm-mark-row]');

        if (!field || field.disabled) {
            return;
        }

        const row = Number.parseInt(field.dataset.jsmMarkRow, 10);

        if (!Number.isInteger(row) || row < 0) {
            return;
        }

        const checkbox = document.getElementById(`cb${row}`);

        if (!checkbox || checkbox.checked) {
            return;
        }

        checkbox.checked = true;

        if (window.Joomla && typeof Joomla.isChecked === 'function') {
            Joomla.isChecked(true);
        }
    });
})();
