<?php
/** SportsManagement legacy compatibility bridge for the statistic table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\StatisticTable;

if (!class_exists(StatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/StatisticTable.php';
}

if (!class_exists('sportsmanagementTableStatistic', false)) {
    class_alias(StatisticTable::class, 'sportsmanagementTableStatistic');
}
