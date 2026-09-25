<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Teamplayers model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayersModel;

if (!class_exists(TeamplayersModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TeamplayersModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(TeamplayersModel::class)) {
    throw new \RuntimeException('SportsManagement native Teamplayers model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelteamplayers', false)) {
    class_alias(TeamplayersModel::class, 'sportsmanagementModelteamplayers');
}
