<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Projectteam model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel;

if (!class_exists(ProjectteamModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectteamModel.php';
}

if (!class_exists(ProjectteamModel::class)) {
    throw new \RuntimeException('SportsManagement native Projectteam model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelprojectteam', false)) {
    class_alias(ProjectteamModel::class, 'sportsmanagementModelprojectteam');
}
