<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 match staff form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstaffModel;

if (!class_exists(MatchstaffModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatchstaffModel.php';
}

if (!class_exists(MatchstaffModel::class)) {
    throw new \RuntimeException('SportsManagement native Matchstaff model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelmatchstaff', false)) {
    class_alias(MatchstaffModel::class, 'sportsmanagementModelmatchstaff');
}
