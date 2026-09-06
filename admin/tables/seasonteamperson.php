<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 SeasonteampersonTable.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonteampersonTable;

if (!class_exists(SeasonteampersonTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SeasonteampersonTable.php';
}

if (!class_exists('sportsmanagementTableseasonteamperson', false)) {
    class_alias(SeasonteampersonTable::class, 'sportsmanagementTableseasonteamperson');
}
