<?php
/**
 * Legacy compatibility bridge for the native Rosteralltime model.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RosteralltimeModel;

if (!class_exists(RosteralltimeModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RosteralltimeModel.php';
}

if (!class_exists('sportsmanagementModelRosteralltime', false)) {
    class_alias(RosteralltimeModel::class, 'sportsmanagementModelRosteralltime');
}
