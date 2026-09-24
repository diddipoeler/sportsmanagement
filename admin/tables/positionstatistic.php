<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Positionstatistic table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PositionstatisticTable;

if (!class_exists(PositionstatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PositionstatisticTable.php';
}

if (!class_exists(PositionstatisticTable::class)) {
    throw new \RuntimeException('SportsManagement native Positionstatistic table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablePositionStatistic', false)) {
    class_alias(PositionstatisticTable::class, 'sportsmanagementTablePositionStatistic');
}
