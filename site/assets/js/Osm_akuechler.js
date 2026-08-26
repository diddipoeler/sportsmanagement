document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.osm-map').forEach((mapElement) => {
        const latitude = Number.parseFloat(mapElement.dataset.lat);
        const longitude = Number.parseFloat(mapElement.dataset.lon);
        const zoom = Number.parseInt(mapElement.dataset.zoom, 10);

        if (!Number.isFinite(latitude) || !Number.isFinite(longitude) || !Number.isFinite(zoom)) {
            return;
        }

        const map = new L.Map(mapElement, osmAKuechlerConfig.mapConfig);
        map.attributionControl.setPrefix('');

        const baseLayer = new L.TileLayer(
            osmAKuechlerConfig.server,
            osmAKuechlerConfig.tileConfig,
        );
        const coordinates = new L.LatLng(latitude, longitude);

        map.setView(coordinates, zoom).addLayer(baseLayer);

        Array.from(mapElement.children)
            .filter((element) => element.classList.contains('osm-point'))
            .forEach((pointElement) => {
                const pointLatitude = Number.parseFloat(pointElement.dataset.lat);
                const pointLongitude = Number.parseFloat(pointElement.dataset.lon);

                if (Number.isFinite(pointLatitude) && Number.isFinite(pointLongitude)) {
                    const marker = new L.Marker(new L.LatLng(pointLatitude, pointLongitude));
                    map.addLayer(marker);
                    marker.bindPopup(pointElement.innerHTML.replace(/[\n\r]+/g, ''));
                }

                pointElement.remove();
            });
    });
});
