<?php
/** Legacy compatibility bridge for the native administrator player model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayerModel;

if (!class_exists(PlayerModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlayerModel.php';
}

if (!class_exists('sportsmanagementModelplayer', false)) {
    class_alias(PlayerModel::class, 'sportsmanagementModelplayer');
}
