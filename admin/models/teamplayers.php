<?php
/** Legacy compatibility bridge for the native administrator team players model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayersModel;

if (!class_exists(TeamplayersModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TeamplayersModel.php';
}

if (!class_exists('sportsmanagementModelteamplayers', false)) {
    class_alias(TeamplayersModel::class, 'sportsmanagementModelteamplayers');
}
