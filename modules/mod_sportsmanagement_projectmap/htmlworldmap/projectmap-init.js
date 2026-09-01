(() => {
    'use strict';

    const options = window.Joomla && typeof window.Joomla.getOptions === 'function'
        ? window.Joomla.getOptions('mod_sportsmanagement_projectmap.mapdata', {})
        : {};

    window.simplemaps_worldmap_mapdata = options && typeof options === 'object' ? options : {};
})();
