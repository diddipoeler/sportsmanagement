<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictiontemplate model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiontemplateModel;

if (!class_exists(PredictiontemplateModel::class)) {
    foreach ([
        __DIR__ . '/../src/Helper/ActionLogHelper.php',
        __DIR__ . '/../src/Helper/SportsManagementDatabaseResolver.php',
        __DIR__ . '/../src/Service/SportsManagementAdministratorApplicationResolver.php',
        __DIR__ . '/../src/Table/SportsManagementTable.php',
        __DIR__ . '/../src/Table/PredictiontemplateTable.php',
        __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
        __DIR__ . '/../src/Model/PredictiontemplateModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(PredictiontemplateModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictiontemplate model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionTemplate', false)) {
    class_alias(PredictiontemplateModel::class, 'sportsmanagementModelPredictionTemplate');
}
