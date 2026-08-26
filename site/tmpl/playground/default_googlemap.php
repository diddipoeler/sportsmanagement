<?php
/** Native Leaflet map for the Joomla 5/6 playground view. */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$latitude = is_numeric($this->playground->latitude ?? null) ? (float) $this->playground->latitude : null;
$longitude = is_numeric($this->playground->longitude ?? null) ? (float) $this->playground->longitude : null;

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
$name = (string) ($this->playground->name ?? '');
$document = $this->getDocument();
$wa = $document->getWebAssetManager();
$wa->useScript('core');
$wa->registerAndUseStyle(
    'com_sportsmanagement.playground.leaflet',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
);
$wa->registerAndUseScript(
    'com_sportsmanagement.playground.leaflet',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
);
$wa->registerAndUseStyle(
    'com_sportsmanagement.playground.routing',
    'https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css'
);
$wa->registerAndUseScript(
    'com_sportsmanagement.playground.routing',
    'https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js',
    [],
    [],
    ['com_sportsmanagement.playground.leaflet']
);
$wa->registerAndUseScript(
    'com_sportsmanagement.playground.map',
    'components/com_sportsmanagement/assets/js/playground-map.js',
    [],
    ['defer' => true],
    ['core', 'com_sportsmanagement.playground.routing']
);
$document->addScriptOptions('com_sportsmanagement.playground.map', [
    'latitude' => $latitude,
    'longitude' => $longitude,
    'zoom' => $zoom,
    'icon' => $icon,
    'name' => $name,
]);

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS')];
echo $this->loadTemplate('jsm_notes');
?>
<div
    id="mapjsm"
    style="height: <?php echo $height; ?>px; position: relative;"
    itemscope
    itemtype="https://schema.org/Place"
></div>
