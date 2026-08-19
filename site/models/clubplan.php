<?php
/**
 * Legacy compatibility bridge for the native Clubplan model.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubplanModel;

if (!class_exists(ClubplanModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ClubplanModel.php';
}

if (!class_exists('sportsmanagementModelClubPlan', false)) {
    class_alias(ClubplanModel::class, 'sportsmanagementModelClubPlan');
}
