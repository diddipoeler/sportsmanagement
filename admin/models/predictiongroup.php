<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictiongroup model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongroupModel;

if (!class_exists(PredictiongroupModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiongroupModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(PredictiongroupModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictiongroup model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelpredictiongroup', false)) {
    class_alias(PredictiongroupModel::class, 'sportsmanagementModelpredictiongroup');
}
