<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Matchreferee model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchrefereeModel;

if (!class_exists(MatchrefereeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatchrefereeModel.php';
}

if (!class_exists(MatchrefereeModel::class)) {
    throw new \RuntimeException('SportsManagement native Matchreferee model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelmatchreferee', false)) {
    class_alias(MatchrefereeModel::class, 'sportsmanagementModelmatchreferee');
}
