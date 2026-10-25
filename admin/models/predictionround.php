<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictionround model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionroundModel;

if (!class_exists(PredictionroundModel::class)) {
    foreach ([
        __DIR__ . '/../src/Helper/ActionLogHelper.php',
        __DIR__ . '/../src/Helper/SportsManagementDatabaseResolver.php',
        __DIR__ . '/../src/Service/SportsManagementAdministratorApplicationResolver.php',
        __DIR__ . '/../src/Table/SportsManagementTable.php',
        __DIR__ . '/../src/Table/PredictionroundTable.php',
        __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
        __DIR__ . '/../src/Model/PredictionroundModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(PredictionroundModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionround model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionRound', false)) {
    class_alias(PredictionroundModel::class, 'sportsmanagementModelPredictionRound');
}
