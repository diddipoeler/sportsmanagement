(() => {
    'use strict';

    const submitOnChange = () => {
        document.querySelectorAll('[data-jsm-curve-team][data-jsm-submit-on-change="1"]').forEach((select) => {
            select.addEventListener('change', () => {
                const form = select.closest('form');

                if (form) {
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            });
        });
    };

    const initialiseChart = () => {
        if (typeof window.Chart === 'undefined' || typeof window.Joomla === 'undefined') {
            return;
        }

        const canvas = document.querySelector('[data-jsm-curve-chart]');
        if (!canvas || canvas.dataset.jsmChartInitialised === '1') {
            return;
        }

        const config = Joomla.getOptions('com_sportsmanagement.curve.chart', null);
        const context = canvas.getContext('2d');

        if (!config || !context) {
            return;
        }

        new Chart(context, config);
        canvas.dataset.jsmChartInitialised = '1';
    };

    const initialise = () => {
        submitOnChange();
        initialiseChart();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }
})();
