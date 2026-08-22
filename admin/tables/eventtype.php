<?php
/** SportsManagement legacy compatibility bridge for the event type table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\EventtypeTable;

if (!class_exists(EventtypeTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/EventtypeTable.php';
}

if (!class_exists('sportsmanagementTableEventtype', false)) {
    class_alias(EventtypeTable::class, 'sportsmanagementTableEventtype');
}
