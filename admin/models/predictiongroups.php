<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictiongroups list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongroupsModel;

if (!class_exists(PredictiongroupsModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiongroupsModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(PredictiongroupsModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictiongroups model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelpredictiongroups', false)) {
    class_alias(PredictiongroupsModel::class, 'sportsmanagementModelpredictiongroups');
}
