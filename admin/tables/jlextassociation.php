<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Jlextassociation table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextassociationTable;

if (!class_exists(JlextassociationTable::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JlextassociationTable.php',
    ] as $nativeTable) {
        if (is_file($nativeTable)) {
            require_once $nativeTable;
        }
    }
}

if (!class_exists(JlextassociationTable::class)) {
    throw new \RuntimeException('SportsManagement native Jlextassociation table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablejlextassociation', false)) {
    class_alias(JlextassociationTable::class, 'sportsmanagementTablejlextassociation');
}
