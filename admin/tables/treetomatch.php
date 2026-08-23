<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetomatchTable;

if (!class_exists(TreetomatchTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TreetomatchTable.php';
}

if (!class_exists('sportsmanagementTableTreetoMatch', false)) {
    class_alias(TreetomatchTable::class, 'sportsmanagementTableTreetoMatch');
}
