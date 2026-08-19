<?php
/**
 * Legacy compatibility bridge for the native Leaguechampionoverview model.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\LeaguechampionoverviewModel;

if (!class_exists(LeaguechampionoverviewModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/LeaguechampionoverviewModel.php';
}

if (!class_exists('sportsmanagementModelleaguechampionoverview', false)) {
    class_alias(LeaguechampionoverviewModel::class, 'sportsmanagementModelleaguechampionoverview');
}
