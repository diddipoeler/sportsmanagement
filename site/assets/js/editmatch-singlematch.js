(function () {
    'use strict';

    function syncBoxChecked(form) {
        const boxChecked = form.querySelector('input[name="boxchecked"]');

        if (!boxChecked) {
            return;
        }

        boxChecked.value = String(form.querySelectorAll('input[name="cid[]"]:checked').length);
    }

    function markRowChanged(form, checkboxId) {
        if (!checkboxId) {
            return;
        }

        const checkbox = form.querySelector(`#${CSS.escape(checkboxId)}`);

        if (checkbox instanceof HTMLInputElement) {
            checkbox.checked = true;
            syncBoxChecked(form);
        }
    }

    function submitSingleMatch(button) {
        const form = button.form;
        const task = button.dataset.singlematchSubmitTask || '';

        if (!form || !task || !window.Joomla || typeof window.Joomla.submitform !== 'function') {
            return false;
        }

        if (button.dataset.closeBeforeSubmit === '1') {
            const close = form.querySelector('#close');

            if (close) {
                close.value = '1';
            }
        }

        window.Joomla.submitform(task, form);
        return false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('adminForm');

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        form.querySelectorAll('[data-row-checkbox]').forEach((field) => {
            field.addEventListener('change', () => {
                markRowChanged(form, field.dataset.rowCheckbox || '');
            });
        });

        form.querySelectorAll('input[name="cid[]"]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => syncBoxChecked(form));
        });

        const checkAll = form.querySelector('#checkall-toggle, input[name="checkall-toggle"]');

        if (checkAll) {
            checkAll.addEventListener('click', () => {
                window.setTimeout(() => syncBoxChecked(form), 0);
            });
        }

        form.querySelectorAll('[data-singlematch-submit-task]').forEach((button) => {
            button.addEventListener('click', () => submitSingleMatch(button));
        });

        syncBoxChecked(form);
    });
}());
