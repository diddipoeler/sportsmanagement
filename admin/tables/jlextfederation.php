<?php
/** Legacy compatibility bridge for the native JlextfederationTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextfederationTable;

if (!class_exists(JlextfederationTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JlextfederationTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(JlextfederationTable::class) && !class_exists('sportsmanagementTablejlextfederation', false)) {
    class_alias(JlextfederationTable::class, 'sportsmanagementTablejlextfederation');
}
