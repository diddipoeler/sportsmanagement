<?php
/** Legacy compatibility bridge for the native administrator team player model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayerModel;

if (!class_exists(TeamplayerModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TeamplayerModel.php';
}

if (!class_exists('sportsmanagementModelteamplayer', false)) {
    class_alias(TeamplayerModel::class, 'sportsmanagementModelteamplayer');
}
