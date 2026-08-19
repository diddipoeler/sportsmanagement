<?php
/** Legacy compatibility bridge for the native administrator Seasonteam form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonteamModel;

if (!class_exists(SeasonteamModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SeasonteamModel.php';
}

if (!class_exists('sportsmanagementModelseasonteam', false)) {
    class_alias(SeasonteamModel::class, 'sportsmanagementModelseasonteam');
}
