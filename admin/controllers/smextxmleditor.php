<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extended XML editor controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmextxmleditorController;

if (!class_exists(SmextxmleditorController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/SmextxmleditorController.php';
    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Smextxmleditor controller file is missing.', 500);
    }
    require_once $nativeController;
}

if (!class_exists(SmextxmleditorController::class)) {
    throw new \RuntimeException('SportsManagement native Smextxmleditor controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllersmextxmleditor', false)) {
    class_alias(SmextxmleditorController::class, 'sportsmanagementControllersmextxmleditor');
}
