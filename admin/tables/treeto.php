<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetoTable;

if (!class_exists(TreetoTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TreetoTable.php';
}

if (!class_exists('sportsmanagementTableTreeto', false)) {
    class_alias(TreetoTable::class, 'sportsmanagementTableTreeto');
}
