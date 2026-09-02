(() => {
    'use strict';

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const button = event.target.closest('[data-jsm-mediawiki]');

        if (!button) {
            return;
        }

        const content = button.getAttribute('data-jsm-mediawiki-content') || '';

        if (content !== '') {
            window.alert(content);
        }
    });
})();
