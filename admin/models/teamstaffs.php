<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Teamstaffs model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamstaffsModel;

if (!class_exists(TeamstaffsModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TeamstaffsModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(TeamstaffsModel::class)) {
    throw new \RuntimeException('SportsManagement native Teamstaffs model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTeamStaffs', false)) {
    class_alias(TeamstaffsModel::class, 'sportsmanagementModelTeamStaffs');
}
