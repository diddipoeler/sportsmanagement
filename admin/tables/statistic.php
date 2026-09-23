<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** SportsManagement legacy compatibility bridge for the statistic table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\StatisticTable;

if (!class_exists(StatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/StatisticTable.php';
}

if (!class_exists('sportsmanagementTableStatistic', false)) {
    class_alias(StatisticTable::class, 'sportsmanagementTableStatistic');
}
