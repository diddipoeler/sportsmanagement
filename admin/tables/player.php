<?php
/** Legacy compatibility bridge for the native SportsManagement person table. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PersonTable;

if (!class_exists(PersonTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PersonTable.php';
}

if (!class_exists('sportsmanagementTableplayer', false)) {
    class_alias(PersonTable::class, 'sportsmanagementTableplayer');
}
