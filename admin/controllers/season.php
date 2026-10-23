<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 Season controller.
 *
 * The active implementation lives in admin/src/Controller/SeasonController.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SeasonController;

if (!class_exists(SeasonController::class)) {
    foreach ([
        __DIR__ . '/../src/Controller/SportsManagementFormController.php',
        __DIR__ . '/../src/Controller/SeasonController.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator controller dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(SeasonController::class)) {
    throw new \RuntimeException('SportsManagement native Season controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerseason', false)) {
    class_alias(SeasonController::class, 'sportsmanagementControllerseason');
}
