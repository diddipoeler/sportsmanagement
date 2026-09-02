(() => {
    'use strict';

    const initialise = (container) => {
        if (typeof window.L === 'undefined') {
            return;
        }

        let markers = [];
        try {
            markers = JSON.parse(container.dataset.rankingMarkers || '[]');
        } catch (error) {
            return;
        }

        if (!Array.isArray(markers) || markers.length === 0) {
            return;
        }

        const zoom = Math.max(1, Math.min(18, Number.parseInt(container.dataset.rankingZoom || '13', 10) || 13));
        const iconWidth = Math.max(12, Math.min(100, Number.parseInt(container.dataset.rankingIconWidth || '20', 10) || 20));
        const useClubIcon = container.dataset.rankingUseClubIcon === '1';
        const fallbackIcon = String(container.dataset.rankingFallbackIcon || '').trim();
        const map = L.map(container).setView([markers[0].lat, markers[0].lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const bounds = [];

        markers.forEach((item) => {
            const options = {};
            const iconUrl = useClubIcon ? String(item.logo || '') : fallbackIcon;

            if (iconUrl) {
                options.icon = L.icon({
                    iconUrl,
                    iconSize: [iconWidth, iconWidth],
                    iconAnchor: [Math.floor(iconWidth / 2), iconWidth],
                });
            }

            const marker = L.marker([item.lat, item.lng], options).addTo(map);
            const popup = document.createElement('div');
            const name = document.createElement('strong');
            name.textContent = String(item.name || '');
            popup.appendChild(name);

            if (item.logo) {
                popup.appendChild(document.createElement('br'));
                const image = document.createElement('img');
                image.src = String(item.logo);
                image.alt = String(item.name || '');
                image.width = 50;
                image.loading = 'lazy';
                popup.appendChild(image);
            }

            marker.bindPopup(popup);
            bounds.push([item.lat, item.lng]);
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, {padding: [20, 20]});
        }
    };

    const boot = () => document.querySelectorAll('[data-jsm-ranking-map]').forEach(initialise);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, {once: true});
    } else {
        boot();
    }
})();
