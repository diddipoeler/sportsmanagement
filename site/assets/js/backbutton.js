(() => {
    'use strict';

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-jsm-back-button]');

        if (!button) {
            return;
        }

        window.history.back();
    });
})();
