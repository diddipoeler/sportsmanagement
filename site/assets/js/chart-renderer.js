(() => {
    'use strict';

    const prepareDataset = (dataset) => {
        if (
            dataset
            && dataset.backgroundAlpha !== undefined
            && dataset.borderColor
            && window.Chart
            && Chart.helpers
            && typeof Chart.helpers.color === 'function'
        ) {
            dataset.backgroundColor = Chart.helpers
                .color(dataset.borderColor)
                .alpha(Number(dataset.backgroundAlpha))
                .rgbString();
            delete dataset.backgroundAlpha;
        }

        return dataset;
    };

    const initialise = () => {
        if (typeof window.Chart === 'undefined' || typeof window.Joomla === 'undefined') {
            return;
        }

        document.querySelectorAll('[data-jsm-chart]').forEach((canvas) => {
            if (canvas.dataset.jsmChartInitialised === '1') {
                return;
            }

            const optionKey = String(canvas.dataset.jsmChartOptions || '').trim();
            if (optionKey === '') {
                return;
            }

            const config = Joomla.getOptions(optionKey, null);
            if (!config || !config.data) {
                return;
            }

            if (Array.isArray(config.data.datasets)) {
                config.data.datasets = config.data.datasets.map(prepareDataset);
            }

            const context = canvas.getContext('2d');
            if (!context) {
                return;
            }

            new Chart(context, config);
            canvas.dataset.jsmChartInitialised = '1';
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }
})();
