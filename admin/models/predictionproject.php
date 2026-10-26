<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictionproject model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionprojectModel;

if (!class_exists(PredictionprojectModel::class)) {
    foreach ([
        __DIR__ . '/../src/Helper/ActionLogHelper.php',
        __DIR__ . '/../src/Helper/SportsManagementDatabaseResolver.php',
        __DIR__ . '/../src/Service/SportsManagementAdministratorApplicationResolver.php',
        __DIR__ . '/../src/Table/SportsManagementTable.php',
        __DIR__ . '/../src/Table/PredictionprojectTable.php',
        __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
        __DIR__ . '/../src/Model/PredictionprojectModel.php',
    ] as $nativeModel) {
        if (!is_file($nativeModel)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($nativeModel), 500);
        }

        require_once $nativeModel;
    }
}

if (!class_exists(PredictionprojectModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionproject model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelpredictionproject', false)) {
    class_alias(PredictionprojectModel::class, 'sportsmanagementModelpredictionproject');
}
