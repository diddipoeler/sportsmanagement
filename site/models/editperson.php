<?php
/**
 * Legacy compatibility bridge for the native frontend Editperson model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel;

if (!class_exists(EditpersonModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/SportsManagementDatabaseResolver.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PersonTable.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditpersonModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(EditpersonModel::class)) {
    throw new \RuntimeException('SportsManagement native Editperson model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelEditPerson', false)) {
    class_alias(EditpersonModel::class, 'sportsmanagementModelEditPerson');
}
