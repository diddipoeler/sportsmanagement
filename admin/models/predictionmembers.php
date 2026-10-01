<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction members list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionmembersModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
    PredictionmembersModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionmembersModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(PredictionmembersModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionmembers model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionMembers', false)) {
    class_alias(PredictionmembersModel::class, 'sportsmanagementModelPredictionMembers');
}
