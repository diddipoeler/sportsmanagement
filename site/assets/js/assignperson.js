(() => {
    'use strict';

    const form = document.querySelector('[data-jsm-assign-person-form]');
    const assignButton = form?.querySelector('[data-jsm-assign-person]');

    if (!form || !assignButton) {
        return;
    }

    const closeParentDialog = () => {
        if (window.parent === window) {
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

    const assign = () => {
        const parentForm = window.top.document.forms.adminForm;
        const project = form.querySelector('#prjid');
        const team = form.querySelector('#xtid');

        if (!parentForm || !project || !team) {
            return;
        }

        const projectField = parentForm.elements.namedItem('project_id');
        const teamField = parentForm.elements.namedItem('team_id');
        const assignField = parentForm.elements.namedItem('assignperson');

        if (!projectField || !teamField || !assignField) {
            return;
        }

        projectField.value = project.value;
        teamField.value = team.value;
        assignField.value = '1';
        closeParentDialog();
    };

    window.SportsManagementAssignPerson = {assign, closeParentDialog};
    assignButton.addEventListener('click', assign);
})();
