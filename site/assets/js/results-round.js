(() => {
    'use strict';

    const initialise = () => {
        document.querySelectorAll('[data-jsm-results-round]').forEach((selector) => {
            selector.addEventListener('change', () => {
                const form = selector.form;

                if (form) {
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }
})();
