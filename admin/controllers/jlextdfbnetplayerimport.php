<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 DFB.net import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdfbnetplayerimportController;

if (!class_exists(JlextdfbnetplayerimportController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JlextdfbnetplayerimportController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Jlextdfbnetplayerimport controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JlextdfbnetplayerimportController::class)) {
    throw new \RuntimeException('SportsManagement native Jlextdfbnetplayerimport controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlextdfbnetplayerimport', false)) {
    class_alias(JlextdfbnetplayerimportController::class, 'sportsmanagementControllerjlextdfbnetplayerimport');
}
