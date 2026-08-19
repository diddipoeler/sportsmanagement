<?php
/** Legacy compatibility bridge for the native administrator Statistic model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\StatisticModel;

if (!class_exists(StatisticModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/StatisticModel.php';
}

if (!class_exists('sportsmanagementModelstatistic', false)) {
    class_alias(StatisticModel::class, 'sportsmanagementModelstatistic');
}
