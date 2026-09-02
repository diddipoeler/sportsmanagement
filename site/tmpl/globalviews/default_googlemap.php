<?php
/**
 * Shared Joomla 5/6 map layout for SportsManagement project views.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();
$this->view = $app->getInput()->getCmd('view');
$this->showmap = false;

$document = $this->getDocument();
$assets = $document->getWebAssetManager();
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$coordinate = static fn (mixed $value): ?float => is_numeric($value) ? (float) $value : null;
$hasCoordinate = static fn (?float $latitude, ?float $longitude): bool => $latitude !== null
    && $longitude !== null
    && abs($latitude) > 0.00000001
    && abs($longitude) > 0.00000001;

$mapType = 'https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}';

switch ((string) ($this->mapconfig['default_map_type'] ?? 'G_HYBRID_MAP')) {
    case 'G_NORMAL_MAP':
        $mapType = 'https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}';
        break;
    case 'G_SATELLITE_MAP':
        $mapType = 'https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}';
        break;
    case 'G_TERRAIN_MAP':
        $mapType = 'https://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}';
        break;
}

$useLeaflet = !empty($this->config['use_which_map']);

if ($useLeaflet) {
    $leafletVersion = preg_replace('/[^0-9A-Za-z.\-_]/', '', (string) ($this->leaflet_version ?? ''));
    $locateVersion = preg_replace('/[^0-9A-Za-z.\-_]/', '', (string) ($this->leaflet_locatecontrol ?? ''));
    $routingVersion = preg_replace('/[^0-9A-Za-z.\-_]/', '', (string) ($this->leaflet_routing_machine ?? ''));

    if ($leafletVersion === '' || $locateVersion === '' || $routingVersion === '') {
        return;
    }

    $assets->registerAndUseStyle(
        'com_sportsmanagement.leaflet',
        'https://unpkg.com/leaflet@' . $leafletVersion . '/dist/leaflet.css',
        [],
        array_filter([
            'integrity' => (string) ($this->leaflet_css_integrity ?? ''),
            'crossorigin' => 'anonymous',
        ])
    );
    $assets->registerAndUseStyle(
        'com_sportsmanagement.leaflet-locate',
        'https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@' . $locateVersion . '/dist/L.Control.Locate.min.css'
    );
    $assets->registerAndUseStyle(
        'com_sportsmanagement.leaflet-routing',
        'https://unpkg.com/leaflet-routing-machine@' . $routingVersion . '/dist/leaflet-routing-machine.css'
    );

    $assets->registerAndUseScript(
        'com_sportsmanagement.leaflet',
        'https://unpkg.com/leaflet@' . $leafletVersion . '/dist/leaflet.js',
        [],
        array_filter([
            'integrity' => (string) ($this->leaflet_js_integrity ?? ''),
            'crossorigin' => 'anonymous',
        ])
    );
    $assets->registerAndUseScript(
        'com_sportsmanagement.leaflet-locate',
        'https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@' . $locateVersion . '/dist/L.Control.Locate.min.js',
        [],
        ['defer' => true],
        ['com_sportsmanagement.leaflet']
    );
    $assets->registerAndUseScript(
        'com_sportsmanagement.leaflet-routing',
        'https://unpkg.com/leaflet-routing-machine@' . $routingVersion . '/dist/leaflet-routing-machine.js',
        [],
        ['defer' => true],
        ['com_sportsmanagement.leaflet']
    );
    $assets->registerAndUseStyle(
        'com_sportsmanagement.site.globalmap',
        'components/com_sportsmanagement/assets/css/globalmap.css',
        ['version' => 'auto']
    );
    $assets->registerAndUseScript(
        'com_sportsmanagement.site.globalmap',
        'components/com_sportsmanagement/assets/js/globalmap.js',
        ['version' => 'auto'],
        ['defer' => true],
        [
            'core',
            'com_sportsmanagement.leaflet',
            'com_sportsmanagement.leaflet-locate',
            'com_sportsmanagement.leaflet-routing',
        ]
    );

    $markers = [];
    $center = null;
    $fitBounds = false;
    $routing = false;

    if ($this->view === 'playground' || $this->view === 'clubinfo') {
        $entity = $this->view === 'playground' ? ($this->playground ?? null) : ($this->club ?? null);
        $latitude = $coordinate($entity->latitude ?? null);
        $longitude = $coordinate($entity->longitude ?? null);

        if ($hasCoordinate($latitude, $longitude)) {
            $this->showmap = true;
            $center = ['lat' => $latitude, 'lng' => $longitude];
            $markers[] = [
                'lat' => $latitude,
                'lng' => $longitude,
                'popup' => $escape($entity->name ?? ''),
                'iconUrl' => (string) ($this->mapconfig['map_icon'] ?? ''),
            ];
            $routing = true;
        }
    } elseif (in_array($this->view, ['ranking', 'resultsranking', 'resultsmatrix'], true)) {
        foreach (($this->allteams ?? []) as $row) {
            $latitude = $coordinate($row->latitude ?? null);
            $longitude = $coordinate($row->longitude ?? null);

            if (!$hasCoordinate($latitude, $longitude)) {
                continue;
            }

            $teamName = (string) ($row->team_name ?? '');
            $logo = (string) ($row->logo_big ?? '');
            $popup = $escape($teamName);

            if ($logo !== '') {
                $popup .= '<br>' . HTMLHelper::_('image', $logo, $teamName, ['width' => '50']);
            }

            $marker = [
                'lat' => $latitude,
                'lng' => $longitude,
                'popup' => $popup,
                'iconUrl' => (string) ($this->mapconfig['map_icon'] ?? ''),
            ];

            if (!empty($this->mapconfig['map_ranking_club_icon']) && $logo !== '') {
                $marker['iconUrl'] = rtrim((string) Uri::root(), '/') . '/' . ltrim($logo, '/');
                $marker['iconSize'] = [
                    (int) ($this->mapconfig['map_ranking_club_icon_width'] ?? 50),
                    (int) ($this->mapconfig['map_ranking_club_icon_width'] ?? 50),
                ];
            }

            $markers[] = $marker;
            $center ??= ['lat' => $latitude, 'lng' => $longitude];
        }

        $this->showmap = $markers !== [];
        $fitBounds = $markers !== [];
    }

    $document->addScriptOptions('com_sportsmanagement.globalmap', [
        'provider' => 'leaflet',
        'containerId' => 'mapjsm',
        'height' => max(50, (int) ($this->mapconfig['map_height'] ?? 500)),
        'emptySize' => 50,
        'center' => $center,
        'zoom' => $this->view === 'ranking' || $this->view === 'resultsranking' || $this->view === 'resultsmatrix' ? 8 : 16,
        'maxZoom' => (int) ($this->mapconfig['map_zoom'] ?? 18),
        'tileUrl' => $mapType,
        'markers' => $markers,
        'fitBounds' => $fitBounds,
        'routing' => $routing,
        'locateControl' => true,
        'ipLocationUrl' => 'https://ipinfo.io/geo',
    ]);

    $this->notes = [Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS')];
    echo $this->loadTemplate('jsm_notes');
    ?>
    <?php if ($this->view === 'clubinfo' && $this->showmap) : ?>
        <span class="visually-hidden" itemprop="name"><?php echo $escape($this->club->name ?? ''); ?></span>
        <div itemprop="geo" itemscope itemtype="https://schema.org/GeoCoordinates">
            <meta itemprop="latitude" content="<?php echo $escape($center['lat']); ?>">
            <meta itemprop="longitude" content="<?php echo $escape($center['lng']); ?>">
        </div>
    <?php endif; ?>
    <div
        id="mapjsm"
        class="jsm-globalmap"
        itemscope
        itemtype="https://schema.org/Place"
    ></div>
    <?php

    return;
}

$sef = (bool) $app->get('sef', false);
$googleMapPluginEnabled = PluginHelper::isEnabled('system', 'plugin_googlemap3');
$useNativeGoogleMap = !$googleMapPluginEnabled || $sef;
?>
<div class="<?php echo $escape($this->divclassrow ?? 'row'); ?>" id="jsmgooglemap">
    <div class="col-12">
        <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?></h4>

        <?php if ($useNativeGoogleMap) : ?>
            <?php
            $assets->registerAndUseStyle(
                'com_sportsmanagement.site.globalmap',
                'components/com_sportsmanagement/assets/css/globalmap.css',
                ['version' => 'auto']
            );
            $assets->registerAndUseScript(
                'com_sportsmanagement.google-maps',
                'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places'
            );
            $assets->registerAndUseScript(
                'com_sportsmanagement.site.globalmap',
                'components/com_sportsmanagement/assets/js/globalmap.js',
                ['version' => 'auto'],
                ['defer' => true],
                ['core', 'com_sportsmanagement.google-maps']
            );

            $markers = [];
            $center = null;
            $mode = 'multi';

            if ($this->view === 'clubinfo' || $this->view === 'playground') {
                $mode = 'single';
                $entity = $this->view === 'playground' ? ($this->playground ?? null) : ($this->club ?? null);
                $latitude = $coordinate($entity->latitude ?? null);
                $longitude = $coordinate($entity->longitude ?? null);

                if ($hasCoordinate($latitude, $longitude)) {
                    $this->showmap = true;
                    $center = ['lat' => $latitude, 'lng' => $longitude];
                }
            } else {
                foreach (($this->allteams ?? []) as $row) {
                    $latitude = $coordinate($row->latitude ?? null);
                    $longitude = $coordinate($row->longitude ?? null);

                    if (!$hasCoordinate($latitude, $longitude)) {
                        continue;
                    }

                    $teamName = (string) ($row->team_name ?? '');
                    $logo = (string) ($row->logo_big ?? '');
                    $popup = '<div class="jsm-globalmap-popup"><h4>' . $escape($teamName) . '</h4>';

                    if ($logo !== '') {
                        $popup .= HTMLHelper::_('image', $logo, $teamName, ['width' => '50']);
                    }

                    $popup .= '</div>';
                    $markers[] = [
                        'lat' => $latitude,
                        'lng' => $longitude,
                        'popup' => $popup,
                    ];
                    $center ??= ['lat' => $latitude, 'lng' => $longitude];
                }

                $this->showmap = $markers !== [];
            }

            $document->addScriptOptions('com_sportsmanagement.globalmap', [
                'provider' => 'google',
                'mode' => $mode,
                'mapId' => $mode === 'single' ? 'mapjsm' : 'map-canvas',
                'panoramaId' => $mode === 'single' ? 'pano' : null,
                'center' => $center,
                'markers' => $markers,
                'zoom' => 14,
                'mapTypeId' => 'hybrid',
                'defaultMarkerIcon' => 'https://maps.google.com/mapfiles/kml/pal2/icon49.png',
            ]);
            ?>

            <?php if ($mode === 'single' && $this->showmap) : ?>
                <?php if ($this->view === 'clubinfo') : ?>
                    <span class="visually-hidden" itemprop="name"><?php echo $escape($this->club->name ?? ''); ?></span>
                    <div itemprop="geo" itemscope itemtype="https://schema.org/GeoCoordinates">
                        <meta itemprop="latitude" content="<?php echo $escape($center['lat']); ?>">
                        <meta itemprop="longitude" content="<?php echo $escape($center['lng']); ?>">
                    </div>
                <?php endif; ?>
                <div class="jsm-globalmap-split">
                    <div id="mapjsm" class="jsm-globalmap-half"></div>
                    <div id="pano" class="jsm-globalmap-half"></div>
                </div>
            <?php elseif ($mode === 'multi') : ?>
                <div id="map-canvas" class="jsm-globalmap-canvas"></div>
            <?php endif; ?>
        <?php else : ?>
            <?php
            $params = "{mosmap kml[0]='" . 'tmp' . DIRECTORY_SEPARATOR . $this->kmlfile . "'}";
            echo HTMLHelper::_('content.prepare', $params);
            ?>
        <?php endif; ?>
    </div>
</div>
