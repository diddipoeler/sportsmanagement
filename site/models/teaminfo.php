<?php
/**
 * Legacy compatibility bridge for the native Teaminfo model.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeaminfoModel;

if (!class_exists(TeaminfoModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TeaminfoModel.php';
}

if (!class_exists('sportsmanagementModelTeamInfo', false)) {
    class_alias(TeaminfoModel::class, 'sportsmanagementModelTeamInfo');
}
