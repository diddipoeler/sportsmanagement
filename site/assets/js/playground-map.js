(() => {
    'use strict';

    const options = window.Joomla && typeof window.Joomla.getOptions === 'function'
        ? (window.Joomla.getOptions('com_sportsmanagement.playground.map', {}) || {})
        : {};
    const latitude = Number(options.latitude);
    const longitude = Number(options.longitude);
    const zoom = Number.parseInt(options.zoom, 10);

    if (!Number.isFinite(latitude) || !Number.isFinite(longitude) || typeof window.L === 'undefined') {
        return;
    }

    const initialise = () => {
        const container = document.getElementById('mapjsm');

        if (!container || container.dataset.jsmMapReady === '1') {
            return;
        }

        container.dataset.jsmMapReady = '1';

        const destination = L.latLng(latitude, longitude);
        const map = L.map(container).setView(destination, Number.isFinite(zoom) ? zoom : 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const markerOptions = {};

        if (options.icon) {
            markerOptions.icon = L.icon({iconUrl: String(options.icon)});
        }

        L.marker(destination, markerOptions)
            .addTo(map)
            .bindPopup(String(options.name || ''));

        if (!navigator.geolocation || !L.Routing) {
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const origin = L.latLng(position.coords.latitude, position.coords.longitude);
                L.marker(origin).addTo(map);
                L.Routing.control({
                    waypoints: [origin, destination],
                    addWaypoints: false,
                    draggableWaypoints: false,
                    routeWhileDragging: false,
                    showAlternatives: false,
                }).addTo(map);
            },
            () => {},
            {enableHighAccuracy: false, timeout: 10000, maximumAge: 300000}
        );
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
