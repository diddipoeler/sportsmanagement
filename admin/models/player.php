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
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
    PlayerModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlayerModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(PlayerModel::class)) {
    throw new \RuntimeException('SportsManagement native Player model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelplayer', false)) {
    class_alias(PlayerModel::class, 'sportsmanagementModelplayer');
}
