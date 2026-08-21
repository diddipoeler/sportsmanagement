<?php
/** Legacy compatibility bridge for the native ProjectrefereeTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectrefereeTable;

if (!class_exists(ProjectrefereeTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ProjectrefereeTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(ProjectrefereeTable::class) && !class_exists('sportsmanagementTableProjectReferee', false)) {
    class_alias(ProjectrefereeTable::class, 'sportsmanagementTableProjectReferee');
}
