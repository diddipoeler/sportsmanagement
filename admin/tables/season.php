<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/SeasonTable.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonTable;

if (!class_exists(SeasonTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SeasonTable.php';
}

if (!class_exists('sportsmanagementTableSeason', false)) {
    class_alias(SeasonTable::class, 'sportsmanagementTableSeason');
}
