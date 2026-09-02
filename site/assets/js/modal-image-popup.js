(() => {
    'use strict';

    let popup = null;
    let previousUrl = '';

    const positiveInteger = (value, fallback) => {
        const parsed = Number.parseInt(String(value || ''), 10);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
    };

    const openPopup = (control) => {
        const url = control.href;
        const availableWidth = Math.max(1, Number(window.screen.availWidth || window.screen.width || 1));
        const availableHeight = Math.max(1, Number(window.screen.availHeight || window.screen.height || 1));
        const width = Math.min(positiveInteger(control.dataset.jsmPopupWidth, Math.round(availableWidth / 2)), availableWidth);
        const height = Math.min(positiveInteger(control.dataset.jsmPopupHeight, Math.round(availableHeight / 2)), availableHeight);
        const left = Math.max(0, Math.round((availableWidth - width) / 2));
        const top = Math.max(0, Math.round((availableHeight - height) / 2));
        const target = control.target || 'SingleSecondaryWindowName';
        const features = `resizable=yes,scrollbars=yes,status=yes,width=${width},height=${height},top=${top},left=${left}`;

        if (!popup || popup.closed || previousUrl !== url) {
            popup = window.open(url, target, features);
        }

        if (popup) {
            popup.focus();
        }

        previousUrl = url;
    };

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const control = event.target.closest('[data-jsm-popup]');
        if (!control) {
            return;
        }

        event.preventDefault();
        openPopup(control);
    });
})();
