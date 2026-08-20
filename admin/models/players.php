<?php
/** Legacy compatibility bridge for the native administrator players list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayersModel;

if (!class_exists(PlayersModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlayersModel.php';
}

if (!class_exists('sportsmanagementModelplayers', false)) {
    class_alias(PlayersModel::class, 'sportsmanagementModelplayers');
}
