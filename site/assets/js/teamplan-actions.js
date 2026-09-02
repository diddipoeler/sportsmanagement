(() => {
    'use strict';

    const isHiddenByDisplay = (element) => window.getComputedStyle(element).display === 'none';

    const setExpanded = (control, expanded) => {
        control.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    };

    const toggleDisplay = (target, control) => {
        const shouldShow = isHiddenByDisplay(target);

        document.querySelectorAll('.jsmeventsshowhide').forEach((element) => {
            if (element === target) {
                element.style.display = shouldShow ? '' : 'none';
                return;
            }

            element.style.display = 'none';
            const otherControl = document.querySelector(
                `[data-jsm-teamplan-toggle][data-jsm-teamplan-target="${CSS.escape(element.id)}"]`
            );

            if (otherControl) {
                setExpanded(otherControl, false);
            }
        });

        setExpanded(control, shouldShow);
    };

    const toggleVisibility = (target, control) => {
        const shouldShow = target.style.visibility === 'hidden'
            || window.getComputedStyle(target).visibility === 'hidden';

        target.style.visibility = shouldShow ? 'visible' : 'hidden';
        setExpanded(control, shouldShow);
    };

    const handleToggle = (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const control = event.target.closest('[data-jsm-teamplan-toggle]');
        if (!control) {
            return;
        }

        event.preventDefault();
        const targetId = String(control.dataset.jsmTeamplanTarget || '');
        const target = targetId !== '' ? document.getElementById(targetId) : null;

        if (!target) {
            return;
        }

        if (control.dataset.jsmTeamplanMode === 'visibility') {
            toggleVisibility(target, control);
            return;
        }

        toggleDisplay(target, control);
    };

    const initialisePrintPreview = () => {
        if (!window.jQuery || typeof window.jQuery.fn.printPreview !== 'function') {
            return;
        }

        const button = document.getElementById('btnPrint');
        if (button) {
            window.jQuery(button).printPreview({obj2print: '#teamplanoutput'});
        }
    };

    const initialisePdfExport = () => {
        const button = document.getElementById('exportButton');
        if (!button) {
            return;
        }

        button.addEventListener('click', () => {
            const element = document.getElementById('teamplanoutput');
            if (!element || typeof window.html2pdf !== 'function') {
                return;
            }

            window.html2pdf().set({
                margin: 1,
                filename: 'teamplan.pdf',
                image: {type: 'jpeg', quality: 0.98},
                html2canvas: {scale: 2},
                jsPDF: {unit: 'in', format: 'A3', orientation: 'landscape'},
            }).from(element).save();
        });
    };

    const initialise = () => {
        document.addEventListener('click', handleToggle);
        initialisePrintPreview();
        initialisePdfExport();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
