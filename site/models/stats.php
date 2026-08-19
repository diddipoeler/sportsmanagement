<?php
/**
 * Legacy compatibility bridge for the native Stats model.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsModel;

if (!class_exists(StatsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/StatsModel.php';
}

if (!class_exists('sportsmanagementModelStats', false)) {
    class_alias(StatsModel::class, 'sportsmanagementModelStats');
}
