<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!$this->predictionKmlUrl) {
    return;
}

$height = max(200, (int) ($this->mapconfig['map_height'] ?? 600));
$mapType = (string) ($this->mapconfig['default_map_type'] ?? 'G_HYBRID_MAP');
$mapType = match ($mapType) {
    'G_NORMAL_MAP' => 'ROADMAP',
    'G_SATELLITE_MAP' => 'SATELLITE',
    'G_TERRAIN_MAP' => 'TERRAIN',
    default => 'HYBRID',
};
$params = "{mosmap mapType='" . $mapType
    . "'|dir='1'|zoomWheel='1'|showEarthMaptype='1'|showNormalMaptype='1'"
    . "|showSatelliteMaptype='1'|showTerrainMaptype='1'|showHybridMaptype='1'"
    . "|kml='" . htmlspecialchars($this->predictionKmlUrl, ENT_QUOTES, 'UTF-8')
    . "'|kmlrenderer='geoxml'|controltype='user'|kmlsidebar='left'|kmlsbwidth='200'"
    . "|lightbox='1'|width='100%'|height='" . $height . "'|overview='1'}";
?>
<div class="w-100">
    <div class="contentheading"><?php echo Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?></div>
    <?php echo HTMLHelper::_('content.prepare', $params); ?>
</div>
