<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator quote text editor controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmquotetxtController;

if (!class_exists(SmquotetxtController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/SmquotetxtController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement administrator controller file is missing: ' . basename($nativeController), 500);
    }

    require_once $nativeController;
}

if (!class_exists(SmquotetxtController::class)) {
    throw new \RuntimeException('SportsManagement native Smquotetxt controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllersmquotetxt', false)) {
    class_alias(SmquotetxtController::class, 'sportsmanagementControllersmquotetxt');
}
