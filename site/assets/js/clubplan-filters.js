(() => {
    'use strict';

    const initialise = () => {
        const form = document.querySelector('[data-jsm-clubplan-filters]');

        if (!form) {
            return;
        }

        const teamArt = form.elements.namedItem('teamartsel');
        const teamProject = form.elements.namedItem('teamprojectssel');
        const teamSeason = form.elements.namedItem('teamseasonssel');
        const dateFields = form.querySelectorAll('[data-jsm-clubplan-date]');

        const updateDateVisibility = () => {
            const teamArtValue = String(teamArt?.value ?? '');
            const teamProjectValue = Number(teamProject?.value ?? 0);
            const teamSeasonValue = Number(teamSeason?.value ?? 0);
            const showDates = teamArtValue === '' && teamProjectValue === 0 && teamSeasonValue === 0;

            dateFields.forEach((field) => {
                field.hidden = !showDates;
            });
        };

        form.addEventListener('change', (event) => {
            if (!(event.target instanceof Element) || !event.target.matches('[data-jsm-clubplan-filter]')) {
                return;
            }

            updateDateVisibility();
        });

        updateDateVisibility();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
