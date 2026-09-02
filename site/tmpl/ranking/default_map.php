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
$markerJson = json_encode(
    $markers,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<section class="ranking-map my-4" aria-labelledby="<?php echo $this->escape($mapId . '-title'); ?>">
    <h3 class="h5" id="<?php echo $this->escape($mapId . '-title'); ?>">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
    </h3>
    <div
        id="<?php echo $this->escape($mapId); ?>"
        style="height:<?php echo $height; ?>px"
        role="region"
        data-jsm-ranking-map
        data-ranking-markers="<?php echo htmlspecialchars((string) $markerJson, ENT_QUOTES, 'UTF-8'); ?>"
        data-ranking-zoom="<?php echo $zoom; ?>"
        data-ranking-icon-width="<?php echo $iconWidth; ?>"
        data-ranking-use-club-icon="<?php echo $useClubIcon ? '1' : '0'; ?>"
        data-ranking-fallback-icon="<?php echo htmlspecialchars($fallbackIcon, ENT_QUOTES, 'UTF-8'); ?>"
    ></div>
</section>
