(() => {
    'use strict';

    const form = document.querySelector('[data-jsm-match-stats-form]');

    if (!form) {
        return;
    }

    const closeParentDialog = (refreshParent = false) => {
        if (window.parent === window) {
            return;
        }

        if (refreshParent) {
            window.parent.location.reload();
            return;
        }

        try {
            const frame = window.frameElement;
            const modalElement = frame ? frame.closest('.modal') : null;
            const BootstrapModal = window.parent.bootstrap?.Modal;

            if (modalElement && BootstrapModal) {
                const modal = BootstrapModal.getInstance(modalElement)
                    || BootstrapModal.getOrCreateInstance(modalElement);

                modal.hide();
                return;
            }
        } catch (error) {
            // Fall through to JoomlaDialog cross-window messaging.
        }

        window.parent.postMessage({messageType: 'joomla:cancel'}, window.location.origin);
    };

    const submit = (task, closeAfterSave = false) => {
        if (closeAfterSave) {
            const closeField = form.elements.namedItem('close');

            if (closeField) {
                closeField.value = '1';
            }
        }

        if (window.Joomla && typeof Joomla.submitform === 'function') {
            Joomla.submitform(task, form);
        }
    };

    window.SportsManagementMatchStats = {closeParentDialog, submit};

    form.addEventListener('click', (event) => {
        const taskButton = event.target.closest('[data-match-stats-task]');

        if (taskButton && form.contains(taskButton)) {
            submit(taskButton.dataset.matchStatsTask || '', taskButton.dataset.closeAfterSave === '1');
            return;
        }

        const cancelButton = event.target.closest('[data-match-stats-cancel]');

        if (cancelButton && form.contains(cancelButton)) {
            closeParentDialog(form.dataset.jsmRefresh === '1');
        }
    });

    if (form.dataset.jsmClose === '1') {
        closeParentDialog(form.dataset.jsmRefresh === '1');
    }
})();
