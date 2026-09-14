<?php
/**
 * SportsManagement legacy compatibility bridge for the native Season table.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/SeasonTable.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonTable;

if (!class_exists(SeasonTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SeasonTable.php';
}

if (!class_exists('sportsmanagementTableSeason', false)) {
    class_alias(SeasonTable::class, 'sportsmanagementTableSeason');
}
