(() => {
    'use strict';

    const form = document.querySelector('[data-jsm-editprojectteam-form]');

    if (!form) {
        return;
    }

    form.addEventListener('click', (event) => {
        const button = event.target.closest('[data-jsm-task]');

        if (!button || !form.contains(button)) {
            return;
        }

        event.preventDefault();

        const skipValidation = button.hasAttribute('data-jsm-skip-validation');
        const validator = document.formvalidator;

        if (!skipValidation && validator && typeof validator.isValid === 'function' && !validator.isValid(form)) {
            return;
        }

        Joomla.submitform(button.dataset.jsmTask, form);
    });
})();
