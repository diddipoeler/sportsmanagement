<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Installhelper controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\InstallhelperController;

if (!class_exists(InstallhelperController::class)) {
    foreach ([
        __DIR__ . '/../src/Controller/SportsManagementFormController.php',
        __DIR__ . '/../src/Controller/InstallhelperController.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator controller dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(InstallhelperController::class)) {
    throw new \RuntimeException('SportsManagement native Installhelper controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerinstallhelper', false)) {
    class_alias(InstallhelperController::class, 'sportsmanagementControllerinstallhelper');
}
