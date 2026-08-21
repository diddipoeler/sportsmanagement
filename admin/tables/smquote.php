<?php
/** Legacy compatibility bridge for the native SmquoteTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\SmquoteTable;

if (!class_exists(SmquoteTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SmquoteTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(SmquoteTable::class) && !class_exists('sportsmanagementTablesmquote', false)) {
    class_alias(SmquoteTable::class, 'sportsmanagementTablesmquote');
}
