<?php
/** SportsManagement legacy compatibility bridge for the position table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PositionTable;

if (!class_exists(PositionTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PositionTable.php';
}

if (!class_exists('sportsmanagementTablePosition', false)) {
    class_alias(PositionTable::class, 'sportsmanagementTablePosition');
}
