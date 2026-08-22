<?php
/** SportsManagement legacy compatibility bridge for the club name table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubnameTable;

if (!class_exists(ClubnameTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ClubnameTable.php';
}

if (!class_exists('sportsmanagementTableclubname', false)) {
    class_alias(ClubnameTable::class, 'sportsmanagementTableclubname');
}
