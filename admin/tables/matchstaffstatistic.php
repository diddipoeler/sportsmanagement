<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Matchstaffstatistic table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstaffstatisticTable;

if (!class_exists(MatchstaffstatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchstaffstatisticTable.php';
}

if (!class_exists(MatchstaffstatisticTable::class)) {
    throw new \RuntimeException('SportsManagement native Matchstaffstatistic table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableMatchStaffStatistic', false)) {
    class_alias(MatchstaffstatisticTable::class, 'sportsmanagementTableMatchStaffStatistic');
}
