(() => {
    'use strict';

    const form = document.getElementById('projectteam-form');

    if (!form || !window.Joomla || typeof Joomla.submitform !== 'function') {
        return;
    }

    Joomla.submitbutton = (task) => {
        const skipValidation = task === 'projectteam.cancel';
        const validator = document.formvalidator;

        if (!skipValidation && validator && typeof validator.isValid === 'function' && !validator.isValid(form)) {
            return false;
        }

        Joomla.submitform(task, form);

        return true;
    };
})();
