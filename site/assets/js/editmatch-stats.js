(function () {
    'use strict';

    function statsForm() {
        return document.getElementById('adminForm');
    }

    function syncBoxChecked(form) {
        const boxChecked = form.querySelector('input[name="boxchecked"]');

        if (!boxChecked) {
            return;
        }

        boxChecked.value = String(form.querySelectorAll('.statcheck:checked, .staffstatcheck:checked').length);
    }

    function markRowChanged(field, rowSelector, checkboxSelector) {
        const row = field.closest(rowSelector);
        const checkbox = row?.querySelector(checkboxSelector);

        if (checkbox) {
            checkbox.checked = true;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = statsForm();

        if (!form) {
            return;
        }

        form.querySelectorAll('.stat').forEach((field) => {
            field.addEventListener('change', () => {
                markRowChanged(field, '.statrow', '.statcheck');
                syncBoxChecked(form);
            });
        });

        form.querySelectorAll('.staffstat').forEach((field) => {
            field.addEventListener('change', () => {
                markRowChanged(field, '.staffstatrow', '.staffstatcheck');
                syncBoxChecked(form);
            });
        });

        form.querySelectorAll('.statcheck, .staffstatcheck').forEach((checkbox) => {
            checkbox.addEventListener('change', () => syncBoxChecked(form));
        });

        syncBoxChecked(form);
    });
}());
