(() => {
    'use strict';

    const value = (id) => (document.getElementById(id)?.value || '').trim();

    const update = () => {
        const target = document.getElementById('jform_geocomplete');

        if (!target) {
            return true;
        }

        const city = [value('jform_zipcode'), value('jform_location')].filter(Boolean).join(' ');
        target.value = [value('jform_address'), city, value('jform_country')].filter(Boolean).join(', ');

        return true;
    };

    window.getlatlonopenstreet = update;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', update, { once: true });
    } else {
        update();
    }
})();
