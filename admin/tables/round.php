<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\RoundTable;

if (!class_exists(RoundTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/RoundTable.php';
}

if (!class_exists('sportsmanagementTableRound', false)) {
    class_alias(RoundTable::class, 'sportsmanagementTableRound');
}
