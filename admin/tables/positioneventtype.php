<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PositioneventtypeTable;

if (!class_exists(PositioneventtypeTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PositioneventtypeTable.php';
}

if (!class_exists('sportsmanagementTablePositioneventtype', false)) {
    class_alias(PositioneventtypeTable::class, 'sportsmanagementTablePositioneventtype');
}
