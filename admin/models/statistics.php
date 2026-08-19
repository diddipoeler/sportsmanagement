<?php
/** Legacy compatibility bridge for the native administrator Statistics model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\StatisticsModel;

if (!class_exists(StatisticsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/StatisticsModel.php';
}

if (!class_exists('sportsmanagementModelStatistics', false)) {
    class_alias(StatisticsModel::class, 'sportsmanagementModelStatistics');
}
