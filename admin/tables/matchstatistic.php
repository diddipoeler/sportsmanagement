<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstatisticTable;

if (!class_exists(MatchstatisticTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchstatisticTable.php';
}

if (!class_exists('sportsmanagementTableMatchStatistic', false)) {
    class_alias(MatchstatisticTable::class, 'sportsmanagementTableMatchStatistic');
}
