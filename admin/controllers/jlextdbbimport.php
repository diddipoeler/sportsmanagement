<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 DBB import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdbbimportController;

if (!class_exists(JlextdbbimportController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JlextdbbimportController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Jlextdbbimport controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JlextdbbimportController::class)) {
    throw new \RuntimeException('SportsManagement native Jlextdbbimport controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlextdbbimport', false)) {
    class_alias(JlextdbbimportController::class, 'sportsmanagementControllerjlextdbbimport');
}
