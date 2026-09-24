<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Matchstatistic table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstatisticTable;

if (!class_exists(MatchstatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchstatisticTable.php';
}

if (!class_exists(MatchstatisticTable::class)) {
    throw new \RuntimeException('SportsManagement native Matchstatistic table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableMatchStatistic', false)) {
    class_alias(MatchstatisticTable::class, 'sportsmanagementTableMatchStatistic');
}
