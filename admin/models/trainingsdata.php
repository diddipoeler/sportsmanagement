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

if (!class_exists(TrainingsdataModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TrainingsdataModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(TrainingsdataModel::class)) {
    throw new \RuntimeException('SportsManagement native Trainingsdata model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeltrainingsdata', false)) {
    class_alias(TrainingsdataModel::class, 'sportsmanagementModeltrainingsdata');
}
