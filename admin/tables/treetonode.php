<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetonodeTable;

if (!class_exists(TreetonodeTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TreetonodeTable.php';
}

if (!class_exists('sportsmanagementTableTreetoNode', false)) {
    class_alias(TreetonodeTable::class, 'sportsmanagementTableTreetoNode');
}
