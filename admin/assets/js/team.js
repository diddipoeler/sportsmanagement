(() => {
    'use strict';

    const form = document.getElementById('team-form');

    if (!form || !window.Joomla || typeof Joomla.submitform !== 'function') {
        return;
    }

    const submitTask = (task, skipValidation = false) => {
        const validator = document.formvalidator;

        if (!skipValidation && validator && typeof validator.isValid === 'function' && !validator.isValid(form)) {
            return false;
        }

        Joomla.submitform(task, form);

        return true;
    };

    Joomla.submitbutton = (task) => submitTask(task, task === 'team.cancel');

    form.addEventListener('change', (event) => {
        const trigger = event.target.closest('[data-jsm-auto-submit]');

        if (!trigger || !form.contains(trigger)) {
            return;
        }

        const task = trigger.dataset.jsmAutoSubmit;

        if (task) {
            submitTask(task);
        }
    });
})();
