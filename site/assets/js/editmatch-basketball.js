(function () {
    'use strict';

    function updateCheckedCount(form) {
        const boxChecked = form.querySelector('#boxchecked');

        if (boxChecked instanceof HTMLInputElement) {
            boxChecked.value = String(form.querySelectorAll('.event-player-check:checked').length);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('adminForm');

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        form.addEventListener('change', (event) => {
            const target = event.target;

            if (!(target instanceof HTMLElement)) {
                return;
            }

            const checkboxId = target.dataset.playerCheckbox || '';

            if (checkboxId) {
                const checkbox = document.getElementById(checkboxId);

                if (checkbox instanceof HTMLInputElement && checkbox.form === form) {
                    checkbox.checked = true;
                }
            }

            if (checkboxId || target.classList.contains('event-player-check')) {
                updateCheckedCount(form);
            }
        });

        updateCheckedCount(form);
    });
}());
