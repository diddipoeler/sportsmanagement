(() => {
    'use strict';

    function selectImage(link) {
        if (!(link instanceof HTMLElement)) {
            return;
        }

        const type = link.dataset.imageType || '';
        const imageName = link.dataset.imageName || '';
        const field = link.dataset.imageField || '';
        const fieldId = link.dataset.imageFieldId || '';
        const callbackName = `selectImage_${type}`;
        let callback = null;

        try {
            callback = window.parent?.[callbackName];
        } catch (error) {
            return;
        }

        if (typeof callback === 'function') {
            callback(imageName, imageName, field, fieldId);
        }
    }

    document.addEventListener('click', (event) => {
        const target = event.target;
        const link = target instanceof Element ? target.closest('[data-jsm-select-image]') : null;

        if (!(link instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();
        selectImage(link);
    });
})();
