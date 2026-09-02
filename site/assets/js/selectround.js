(() => {
    'use strict';

    document.addEventListener('change', (event) => {
        const selector = event.target.closest('[data-jsm-selectround]');

        if (!selector) {
            return;
        }

        const target = String(selector.value || '').trim();

        if (target !== '') {
            window.location.assign(target);
        }
    });
})();
