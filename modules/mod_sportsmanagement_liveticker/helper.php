<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement liveticker module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper;

if (!class_exists(LivetickerHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/LivetickerHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(LivetickerHelper::class)) {
    throw new \RuntimeException('SportsManagement native Liveticker module helper could not be loaded.', 500);
}

if (!class_exists('modTurtushoutHelper', false)) {
    class_alias(LivetickerHelper::class, 'modTurtushoutHelper');
}
