<?php
/** Legacy compatibility bridge for the native administrator team staff model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamstaffModel;

if (!class_exists(TeamstaffModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TeamstaffModel.php';
}

if (!class_exists('sportsmanagementModelteamstaff', false)) {
    class_alias(TeamstaffModel::class, 'sportsmanagementModelteamstaff');
}
