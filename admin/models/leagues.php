<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/LeaguesModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\LeaguesModel;

if (!class_exists(LeaguesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/LeaguesModel.php';
}

if (!class_exists('sportsmanagementModelLeagues', false)) {
    class_alias(LeaguesModel::class, 'sportsmanagementModelLeagues');
}
