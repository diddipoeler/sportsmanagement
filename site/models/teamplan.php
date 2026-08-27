<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/TeamplanModel.php.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\TeamplanModel;

if (!class_exists(TeamplanModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TeamplanEventDataTrait.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TeamplanModel.php';
}

if (!class_exists('sportsmanagementModelTeamPlan', false)) {
    class_alias(TeamplanModel::class, 'sportsmanagementModelTeamPlan');
}
