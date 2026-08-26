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

        if (checkbox instanceof HTMLInputElement) {
            checkbox.checked = true;
        }
    }

    function submitStatsForm(form, task) {
        if (!task || !window.Joomla || typeof window.Joomla.submitform !== 'function') {
            return false;
        }

        window.Joomla.submitform(task, form);
        return false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = statsForm();

        if (!(form instanceof HTMLFormElement)) {
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

        form.querySelectorAll('[data-stats-submit-task]').forEach((button) => {
            button.addEventListener('click', () => {
                submitStatsForm(form, button.dataset.statsSubmitTask || '');
            });
        });

        syncBoxChecked(form);
    });
}());
