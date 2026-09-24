<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Person table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PersonTable;

if (!class_exists(PersonTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PersonTable.php';
}

if (!class_exists(PersonTable::class)) {
    throw new \RuntimeException('SportsManagement native Person table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableplayer', false)) {
    class_alias(PersonTable::class, 'sportsmanagementTableplayer');
}
