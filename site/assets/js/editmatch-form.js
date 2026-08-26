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

    function submitEditMatchForm(form, task) {
        if (!task || !window.Joomla || typeof window.Joomla.submitform !== 'function') {
            return false;
        }

        const validator = document.formvalidator;
        const canSubmit = task === 'editmatch.cancel'
            || !validator
            || typeof validator.isValid !== 'function'
            || validator.isValid(form);

        if (canSubmit) {
            window.Joomla.submitform(task, form);
        }

        return false;
    }

    function initializeForm(form) {
        const selector = form.querySelector('#alt_decision');

        if (selector instanceof HTMLSelectElement) {
            selector.addEventListener('change', () => updateAlternativeDecision(form));
            updateAlternativeDecision(form);
        }

        form.querySelectorAll('[data-editmatch-submit-task]').forEach((button) => {
            button.addEventListener('click', () => {
                submitEditMatchForm(form, button.dataset.editmatchSubmitTask || '');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[data-editmatch-form], form#editmatch, form#editperson').forEach((form) => {
            if (form instanceof HTMLFormElement) {
                initializeForm(form);
            }
        });
    });
}());
