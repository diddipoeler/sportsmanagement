<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Eventtypes controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\EventtypesController;

if (!class_exists(EventtypesController::class)) {
    foreach ([
        __DIR__ . '/../src/Controller/SportsManagementAdminController.php',
        __DIR__ . '/../src/Controller/EventtypesController.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(EventtypesController::class)) {
    throw new \RuntimeException('SportsManagement native Eventtypes controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllereventtypes', false)) {
    class_alias(EventtypesController::class, 'sportsmanagementControllereventtypes');
}
