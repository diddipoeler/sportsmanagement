<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Eventtype table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\EventtypeTable;

if (!class_exists(EventtypeTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/EventtypeTable.php';
}

if (!class_exists(EventtypeTable::class)) {
    throw new \RuntimeException('SportsManagement native Eventtype table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableEventtype', false)) {
    class_alias(EventtypeTable::class, 'sportsmanagementTableEventtype');
}
