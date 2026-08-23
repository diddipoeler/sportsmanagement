<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstaffstatisticTable;

if (!class_exists(MatchstaffstatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchstaffstatisticTable.php';
}

if (!class_exists('sportsmanagementTableMatchStaffStatistic', false)) {
    class_alias(MatchstaffstatisticTable::class, 'sportsmanagementTableMatchStaffStatistic');
}
