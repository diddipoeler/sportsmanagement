<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamstatsModel;

if (!class_exists(TeamstatsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TeamstatsModel.php';
}

if (!class_exists('sportsmanagementModelTeamStats', false)) {
    class_alias(TeamstatsModel::class, 'sportsmanagementModelTeamStats');
}
