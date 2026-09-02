(() => {
    'use strict';

    document.addEventListener('change', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const select = event.target.closest('[data-jsm-sectionheader-submit]');

        if (!select || !select.form) {
            return;
        }

        if (typeof select.form.requestSubmit === 'function') {
            select.form.requestSubmit();
            return;
        }

        select.form.submit();
    });
})();
