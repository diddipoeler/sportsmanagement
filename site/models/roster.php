<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RosterModel;

if (!class_exists(RosterModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RosterModel.php';
}

if (!class_exists('sportsmanagementModelRoster', false)) {
    class_alias(RosterModel::class, 'sportsmanagementModelRoster');
}
