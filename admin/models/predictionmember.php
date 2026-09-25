<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction member model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionmemberModel;

if (!class_exists(PredictionmemberModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/ActionLogHelper.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/SportsManagementDatabaseResolver.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/SportsManagementAdministratorApplicationResolver.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionmemberTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionmemberModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(PredictionmemberModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionmember model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelpredictionmember', false)) {
    class_alias(PredictionmemberModel::class, 'sportsmanagementModelpredictionmember');
}
