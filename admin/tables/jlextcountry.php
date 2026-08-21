<?php
/** Legacy compatibility bridge for the native JlextcountryTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextcountryTable;

if (!class_exists(JlextcountryTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JlextcountryTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(JlextcountryTable::class) && !class_exists('sportsmanagementTablejlextcountry', false)) {
    class_alias(JlextcountryTable::class, 'sportsmanagementTablejlextcountry');
}
