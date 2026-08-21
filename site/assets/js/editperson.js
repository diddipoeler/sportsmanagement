(() => {
    'use strict';

    const form = document.querySelector('[data-jsm-editperson-form]');

    if (!form) {
        return;
    }

    const updatePersonPairVisibility = () => {
        const personArt = document.getElementById('jform_request_person_art');
        const showPair = personArt && personArt.value === '2';

        [
            'jform_request_person_id1',
            'jform_request_person_id2',
            'jform_request_person_id1-lbl',
            'jform_request_person_id2-lbl',
        ].forEach((id) => {
            const element = document.getElementById(id);

            if (element) {
                element.style.display = showPair ? '' : 'none';
            }
        });
    };

    // Compatibility for the existing XML field attribute while moving the behaviour to this native asset.
    window.EditshowPersons = updatePersonPairVisibility;

    const personArt = document.getElementById('jform_request_person_art');

    if (personArt) {
        personArt.addEventListener('change', updatePersonPairVisibility);
        updatePersonPairVisibility();
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
