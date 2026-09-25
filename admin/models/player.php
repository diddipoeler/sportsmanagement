<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Player model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayerModel;

if (!class_exists(PlayerModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlayerModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(PlayerModel::class)) {
    throw new \RuntimeException('SportsManagement native Player model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelplayer', false)) {
    class_alias(PlayerModel::class, 'sportsmanagementModelplayer');
}
