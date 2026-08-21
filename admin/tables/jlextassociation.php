<?php
/** Legacy compatibility bridge for the native JlextassociationTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextassociationTable;

if (!class_exists(JlextassociationTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JlextassociationTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(JlextassociationTable::class) && !class_exists('sportsmanagementTablejlextassociation', false)) {
    class_alias(JlextassociationTable::class, 'sportsmanagementTablejlextassociation');
}
