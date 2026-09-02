(() => {
    'use strict';

    const initialise = () => {
        const config = Joomla.getOptions('com_sportsmanagement.clubinfo.map', {});
        const container = document.getElementById('clubinfo-map');

        if (!container || typeof window.L === 'undefined' || container.dataset.jsmMapReady === '1') {
            return;
        }

        const latitude = Number(config.latitude);
        const longitude = Number(config.longitude);
        const zoom = Number(config.zoom ?? 16);

        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
            return;
        }

        container.dataset.jsmMapReady = '1';

        const destination = L.latLng(latitude, longitude);
        const map = L.map(container).setView(destination, zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const markerOptions = {};
        if (config.icon) {
            markerOptions.icon = L.icon({iconUrl: String(config.icon)});
        }

        L.marker(destination, markerOptions)
            .addTo(map)
            .bindPopup(String(config.name ?? ''));

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
