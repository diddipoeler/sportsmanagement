(function () {
    'use strict';

    function updateAlternativeDecision(form) {
        const selector = form.querySelector('#alt_decision');

        if (!(selector instanceof HTMLSelectElement)) {
            return;
        }

        const enabled = selector.value !== '0';
        const container = form.querySelector('#alt_decision_enter');

        if (container instanceof HTMLElement) {
            container.hidden = !enabled;
        }

        ['team1_result_decision', 'team2_result_decision', 'decision_info'].forEach((id) => {
            const field = form.querySelector(`#${id}`);

            if (field instanceof HTMLInputElement) {
                field.disabled = !enabled;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('editmatch');

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const selector = form.querySelector('#alt_decision');

        if (!(selector instanceof HTMLSelectElement)) {
            return;
        }

        selector.addEventListener('change', () => updateAlternativeDecision(form));
        updateAlternativeDecision(form);
    });
}());
