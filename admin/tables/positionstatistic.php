<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PositionstatisticTable;

if (!class_exists(PositionstatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PositionstatisticTable.php';
}

if (!class_exists('sportsmanagementTablePositionStatistic', false)) {
    class_alias(PositionstatisticTable::class, 'sportsmanagementTablePositionStatistic');
}
