<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Eventtype controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\EventtypeController;

if (!class_exists(EventtypeController::class)) {
    foreach ([
        __DIR__ . '/../src/Controller/SportsManagementFormController.php',
        __DIR__ . '/../src/Controller/EventtypeController.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator controller dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(EventtypeController::class)) {
    throw new \RuntimeException('SportsManagement native Eventtype controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllereventtype', false)) {
    class_alias(EventtypeController::class, 'sportsmanagementControllereventtype');
}
