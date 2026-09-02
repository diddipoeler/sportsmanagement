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
$document = $this->getDocument();
$wa = $document->getWebAssetManager();

$document->addScriptOptions('com_sportsmanagement.clubinfo.map', [
    'latitude' => $latitude,
    'longitude' => $longitude,
    'zoom' => $zoom,
    'icon' => $icon,
    'name' => $name,
]);

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
$wa->registerAndUseScript(
    'com_sportsmanagement.clubinfo.map',
    'components/com_sportsmanagement/assets/js/clubinfo-map.js',
    ['version' => 'auto'],
    ['defer' => true],
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
