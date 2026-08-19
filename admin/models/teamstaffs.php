<?php
/** Legacy compatibility bridge for the native administrator team staffs list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamstaffsModel;

if (!class_exists(TeamstaffsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TeamstaffsModel.php';
}

if (!class_exists('sportsmanagementModelTeamStaffs', false)) {
    class_alias(TeamstaffsModel::class, 'sportsmanagementModelTeamStaffs');
}
