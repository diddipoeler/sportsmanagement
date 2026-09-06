<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 SeasonpersonTable.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonpersonTable;

if (!class_exists(SeasonpersonTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SeasonpersonTable.php';
}

if (!class_exists('sportsmanagementTableseasonperson', false)) {
    class_alias(SeasonpersonTable::class, 'sportsmanagementTableseasonperson');
}
