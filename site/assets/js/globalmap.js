(() => {
    'use strict';

    const options = Joomla.getOptions('com_sportsmanagement.globalmap', {});

    if (!options || !options.provider) {
        return;
    }

    const asNumber = (value) => {
        const number = Number(value);
        return Number.isFinite(number) ? number : null;
    };

    const normalisePoint = (point) => {
        if (!point) {
            return null;
        }

        const lat = asNumber(point.lat);
        const lng = asNumber(point.lng);

        return lat === null || lng === null ? null : {lat, lng};
    };

    const initialiseLeaflet = () => {
        const container = document.getElementById(options.containerId || 'mapjsm');

        if (!container || typeof window.L === 'undefined') {
            return;
        }

        const markers = Array.isArray(options.markers) ? options.markers : [];
        const center = normalisePoint(options.center);
        const height = Math.max(50, asNumber(options.height) || 500);

        container.style.height = `${height}px`;

        if (!center || markers.length === 0) {
            const emptySize = Math.max(1, asNumber(options.emptySize) || 50);
            container.style.width = `${emptySize}px`;
            container.style.height = `${emptySize}px`;
            return;
        }

        const map = window.L.map(container).setView(
            [center.lat, center.lng],
            asNumber(options.zoom) || 16
        );

        window.L.tileLayer(options.tileUrl, {
            attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
            maxZoom: asNumber(options.maxZoom) || 18,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        }).addTo(map);

        const bounds = [];

        markers.forEach((markerData) => {
            const point = normalisePoint(markerData);

            if (!point) {
                return;
            }

            const iconOptions = {};

            if (markerData.iconUrl) {
                iconOptions.iconUrl = markerData.iconUrl;
            }

            if (Array.isArray(markerData.iconSize) && markerData.iconSize.length === 2) {
                iconOptions.iconSize = markerData.iconSize.map((value) => asNumber(value) || 50);
            }

            const markerOptions = Object.keys(iconOptions).length > 0
                ? {icon: window.L.icon(iconOptions)}
                : {};
            const marker = window.L.marker([point.lat, point.lng], markerOptions).addTo(map);

            if (markerData.popup) {
                marker.bindPopup(markerData.popup);
            }

            bounds.push([point.lat, point.lng]);
        });

        if (options.fitBounds && bounds.length > 0) {
            map.fitBounds(bounds);
        }

        if (options.locateControl && window.L.control && typeof window.L.control.locate === 'function') {
            window.L.control.locate().addTo(map);
        }

        if (!options.routing || !options.ipLocationUrl || !window.L.Routing) {
            return;
        }

        fetch(options.ipLocationUrl, {headers: {'Accept': 'application/json'}})
            .then((response) => response.ok ? response.json() : Promise.reject(new Error('Location request failed')))
            .then((response) => {
                const parts = String(response.loc || '').split(',');
                const latitude = asNumber(parts[0]);
                const longitude = asNumber(parts[1]);

                if (latitude === null || longitude === null) {
                    return;
                }

                window.L.marker([latitude, longitude]).addTo(map);
                window.L.Routing.control({
                    waypoints: [
                        window.L.latLng(latitude, longitude),
                        window.L.latLng(center.lat, center.lng),
                    ],
                }).addTo(map);
            })
            .catch(() => {});
    };

    const initialiseGoogle = () => {
        if (!window.google || !window.google.maps) {
            return;
        }

        const mapElement = document.getElementById(options.mapId || 'map-canvas');
        const center = normalisePoint(options.center);

        if (!mapElement || !center) {
            return;
        }

        const mapTypeId = options.mapTypeId === 'satellite'
            ? window.google.maps.MapTypeId.SATELLITE
            : window.google.maps.MapTypeId.HYBRID;
        const map = new window.google.maps.Map(mapElement, {
            center,
            draggable: true,
            mapTypeControl: true,
            mapTypeId,
            scrollwheel: true,
            streetViewControl: true,
            zoom: asNumber(options.zoom) || 14,
        });

        if (options.mode === 'single') {
            const panoramaElement = options.panoramaId
                ? document.getElementById(options.panoramaId)
                : null;

            if (!panoramaElement) {
                return;
            }

            const streetViewService = new window.google.maps.StreetViewService();
            streetViewService.getPanorama({location: center, radius: 50}, (data, status) => {
                if (status === window.google.maps.StreetViewStatus.OK) {
                    const panorama = new window.google.maps.StreetViewPanorama(panoramaElement, {
                        position: center,
                        pov: {heading: 34, pitch: 10},
                    });
                    map.setStreetView(panorama);
                    return;
                }

                panoramaElement.remove();
                mapElement.classList.remove('jsm-globalmap-half');
                mapElement.classList.add('jsm-globalmap-full');
            });

            return;
        }

        const markers = Array.isArray(options.markers) ? options.markers : [];
        const bounds = new window.google.maps.LatLngBounds();
        const infoWindow = new window.google.maps.InfoWindow();

        markers.forEach((markerData) => {
            const point = normalisePoint(markerData);

            if (!point) {
                return;
            }

            const marker = new window.google.maps.Marker({
                map,
                position: point,
                icon: options.defaultMarkerIcon || undefined,
            });

            marker.addListener('click', () => {
                infoWindow.setContent(markerData.popup || '');
                infoWindow.open({map, anchor: marker});
            });
            bounds.extend(point);
        });

        if (markers.length > 0) {
            map.fitBounds(bounds);
        }
    };

    const initialise = () => {
        if (options.provider === 'leaflet') {
            initialiseLeaflet();
            return;
        }

        if (options.provider === 'google') {
            initialiseGoogle();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
