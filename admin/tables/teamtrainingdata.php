<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 TeamTrainingData table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable;
use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamTrainingDataTable;

if (!class_exists(SportsManagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
}

if (!class_exists(TeamTrainingDataTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TeamTrainingDataTable.php';
}

if (!class_exists('sportsmanagementTableTeamTrainingData', false)) {
    class_alias(TeamTrainingDataTable::class, 'sportsmanagementTableTeamTrainingData');
}
