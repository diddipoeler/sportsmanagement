<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Statistic controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\StatisticController;

if (!class_exists(StatisticController::class)) {
    foreach ([
        __DIR__ . '/../src/Controller/SportsManagementFormController.php',
        __DIR__ . '/../src/Controller/StatisticController.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator controller dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(StatisticController::class)) {
    throw new \RuntimeException('SportsManagement native Statistic controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerstatistic', false)) {
    class_alias(StatisticController::class, 'sportsmanagementControllerstatistic');
}
