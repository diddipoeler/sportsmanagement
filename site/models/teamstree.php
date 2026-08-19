<?php
/**
 * Legacy compatibility bridge for the native Teamstree model.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamstreeModel;

if (!class_exists(TeamstreeModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TeamstreeModel.php';
}

if (!class_exists('sportsmanagementModelTeamstree', false)) {
    class_alias(TeamstreeModel::class, 'sportsmanagementModelTeamstree');
}
