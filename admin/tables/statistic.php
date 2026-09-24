<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Statistic table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\StatisticTable;

if (!class_exists(StatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/StatisticTable.php';
}

if (!class_exists(StatisticTable::class)) {
    throw new \RuntimeException('SportsManagement native Statistic table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableStatistic', false)) {
    class_alias(StatisticTable::class, 'sportsmanagementTableStatistic');
}
