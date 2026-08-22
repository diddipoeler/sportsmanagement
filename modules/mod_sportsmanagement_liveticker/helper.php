<?php
/** Legacy helper bridge for the Joomla 5/6 SportsManagement liveticker module. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper;

if (!class_exists(LivetickerHelper::class)) {
    require_once __DIR__ . '/src/Helper/LivetickerHelper.php';
}

if (!class_exists('modTurtushoutHelper', false)) {
    class_alias(LivetickerHelper::class, 'modTurtushoutHelper');
}
