<?php
/** Native Leaflet map for the Joomla 5/6 club info view. */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$latitude = is_numeric($this->club->latitude ?? null) ? (float) $this->club->latitude : null;
$longitude = is_numeric($this->club->longitude ?? null) ? (float) $this->club->longitude : null;

if (
    $latitude === null
    || $longitude === null
    || $latitude < -90.0
    || $latitude > 90.0
    || $longitude < -180.0
    || $longitude > 180.0
) {
    return;
}

$height = max(200, (int) ($this->mapconfig['map_height'] ?? 400));
$zoom = min(19, max(1, (int) ($this->mapconfig['map_zoom'] ?? 16)));
$icon = trim((string) ($this->mapconfig['map_icon'] ?? ''));
$name = (string) ($this->club->name ?? '');
$config = json_encode(
    [
        'latitude' => $latitude,
        'longitude' => $longitude,
        'zoom' => $zoom,
        'icon' => $icon,
        'name' => $name,
    ],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
);

if ($config === false) {
    return;
}

$wa = $this->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle(
    'com_sportsmanagement.clubinfo.leaflet',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
);
$wa->registerAndUseScript(
    'com_sportsmanagement.clubinfo.leaflet',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
);
$wa->registerAndUseStyle(
    'com_sportsmanagement.clubinfo.routing',
    'https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css'
);
$wa->registerAndUseScript(
    'com_sportsmanagement.clubinfo.routing',
    'https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js',
    [],
    [],
    ['com_sportsmanagement.clubinfo.leaflet']
);

$script = <<<'JS'
(() => {
    const config = __CONFIG__;

    const initialise = () => {
        const container = document.getElementById('clubinfo-map');

        if (!container || typeof window.L === 'undefined' || container.dataset.jsmMapReady === '1') return;
        container.dataset.jsmMapReady = '1';

        const destination = L.latLng(config.latitude, config.longitude);
        const map = L.map(container).setView(destination, config.zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const markerOptions = {};
        if (config.icon) {
            markerOptions.icon = L.icon({iconUrl: config.icon});
        }

        L.marker(destination, markerOptions)
            .addTo(map)
            .bindPopup(config.name || '');

        if (!navigator.geolocation || !L.Routing) return;

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
JS;

$wa->addInlineScript(
    str_replace('__CONFIG__', $config, $script),
    [],
    [],
    ['com_sportsmanagement.clubinfo.routing']
);

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS')];
echo $this->loadTemplate('jsm_notes');
?>
<div
    id="clubinfo-map"
    style="height: <?php echo $height; ?>px; position: relative;"
    itemscope
    itemtype="https://schema.org/Place"
>
    <meta itemprop="latitude" content="<?php echo $latitude; ?>">
    <meta itemprop="longitude" content="<?php echo $longitude; ?>">
</div>
