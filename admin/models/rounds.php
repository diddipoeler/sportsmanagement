<?php
/** Legacy compatibility bridge for the native administrator rounds list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\RoundsModel;

if (!class_exists(RoundsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RoundsModel.php';
}

if (!class_exists('sportsmanagementModelRounds', false)) {
    class_alias(RoundsModel::class, 'sportsmanagementModelRounds');
}
