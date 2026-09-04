<?php
/**
 * Legacy compatibility bridge for the native administrator Projectteam model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel;

if (!class_exists(ProjectteamModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectteamModel.php';
}

if (!class_exists('sportsmanagementModelprojectteam', false)) {
    class_alias(ProjectteamModel::class, 'sportsmanagementModelprojectteam');
}
