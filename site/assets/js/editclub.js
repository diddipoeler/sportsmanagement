(() => {
    'use strict';

    const form = document.querySelector('[data-jsm-editclub-form]');

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    function submitTask(task, skipValidation = false) {
        if (!task || !window.Joomla || typeof window.Joomla.submitform !== 'function') {
            return;
        }

        const validator = document.formvalidator;

        if (!skipValidation && validator && typeof validator.isValid === 'function' && !validator.isValid(form)) {
            return;
        }

        window.Joomla.submitform(task, form);
    }

    form.addEventListener('click', (event) => {
        const target = event.target;
        const button = target instanceof Element ? target.closest('[data-jsm-task]') : null;

        if (!(button instanceof HTMLElement) || !form.contains(button)) {
            return;
        }

        event.preventDefault();
        submitTask(button.dataset.jsmTask || '', button.hasAttribute('data-jsm-skip-validation'));
    });

    if (form.hasAttribute('data-jsm-auto-cancel')) {
        submitTask('editclub.cancel', true);
    }
})();
