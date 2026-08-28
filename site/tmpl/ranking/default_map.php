<?php
/** Native Leaflet map for ranking teams. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if ($this->mapTeams === []) {
    return;
}

$height = min(1200, max(200, (int) ($this->mapconfig['map_height'] ?? 600)));
$zoom = min(18, max(1, (int) ($this->mapconfig['map_zoom'] ?? 13)));
$iconWidth = min(100, max(12, (int) ($this->mapconfig['map_ranking_club_icon_width'] ?? 20)));
$useClubIcon = !empty($this->mapconfig['map_ranking_club_icon']);
$fallbackIcon = trim((string) ($this->mapconfig['map_icon'] ?? ''));
$markers = [];

foreach ($this->mapTeams as $team) {
    $logo = trim((string) ($team->logo_big ?? $team->logo_small ?? ''));
    if ($logo !== '' && !preg_match('#^https?://#i', $logo)) {
        $logo = rtrim(Uri::root(), '/') . '/' . ltrim($logo, '/');
    }

    $markers[] = [
        'name' => (string) ($team->team_name ?? ''),
        'lat' => (float) ($team->latitude ?? 0),
        'lng' => (float) ($team->longitude ?? 0),
        'logo' => $logo,
    ];
}

$mapId = 'ranking-map-' . (int) ($this->project->id ?? 0);
?>
<section class="ranking-map my-4" aria-labelledby="<?php echo $this->escape($mapId . '-title'); ?>">
    <h3 class="h5" id="<?php echo $this->escape($mapId . '-title'); ?>">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
    </h3>
    <div id="<?php echo $this->escape($mapId); ?>" style="height:<?php echo $height; ?>px" role="region"></div>
</section>
<script type="application/json" id="<?php echo $this->escape($mapId . '-data'); ?>"><?php echo json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
<script>
(() => {
    const container = document.getElementById(<?php echo json_encode($mapId); ?>);
    const data = document.getElementById(<?php echo json_encode($mapId . '-data'); ?>);
    if (!container || !data || typeof L === 'undefined') {
        return;
    }

    let markers;
    try {
        markers = JSON.parse(data.textContent || '[]');
    } catch (error) {
        return;
    }
    if (!Array.isArray(markers) || markers.length === 0) {
        return;
    }

    const map = L.map(container).setView([markers[0].lat, markers[0].lng], <?php echo $zoom; ?>);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const bounds = [];
    markers.forEach((item) => {
        const options = {};
        const configuredFallback = <?php echo json_encode($fallbackIcon, JSON_UNESCAPED_SLASHES); ?>;
        const iconUrl = <?php echo $useClubIcon ? 'item.logo' : 'configuredFallback'; ?>;
        if (iconUrl) {
            options.icon = L.icon({
                iconUrl,
                iconSize: [<?php echo $iconWidth; ?>, <?php echo $iconWidth; ?>],
                iconAnchor: [<?php echo intdiv($iconWidth, 2); ?>, <?php echo $iconWidth; ?>]
            });
        }

        const marker = L.marker([item.lat, item.lng], options).addTo(map);
        const popup = document.createElement('div');
        const name = document.createElement('strong');
        name.textContent = item.name || '';
        popup.appendChild(name);
        if (item.logo) {
            popup.appendChild(document.createElement('br'));
            const image = document.createElement('img');
            image.src = item.logo;
            image.alt = item.name || '';
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
})();
</script>
