<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/LeagueModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\LeagueModel;

if (!class_exists(LeagueModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/LeagueModel.php';
}

if (!class_exists('sportsmanagementModelleague', false)) {
    class_alias(LeagueModel::class, 'sportsmanagementModelleague');
}
