(() => {
    'use strict';

    const initialise = () => {
        const button = document.querySelector('[data-jsm-add-playground-detail]');
        const target = document.querySelector('#playground-detail-new tbody');
        const template = document.getElementById('playground-detail-row-template');

        if (!button || !target || !template) {
            return;
        }

        button.addEventListener('click', () => {
            target.append(template.content.cloneNode(true));
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
