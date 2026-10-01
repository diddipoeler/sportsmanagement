<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Trainingsdata model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TrainingsdataModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
    TrainingsdataModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TrainingsdataModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(TrainingsdataModel::class)) {
    throw new \RuntimeException('SportsManagement native Trainingsdata model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeltrainingsdata', false)) {
    class_alias(TrainingsdataModel::class, 'sportsmanagementModeltrainingsdata');
}
