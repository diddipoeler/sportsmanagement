<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Google calendar import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JsmgcalendarimportController;

if (!class_exists(JsmgcalendarimportController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JsmgcalendarimportController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Jsmgcalendarimport controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JsmgcalendarimportController::class)) {
    throw new \RuntimeException('SportsManagement native Jsmgcalendarimport controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjsmgcalendarImport', false)) {
    class_alias(JsmgcalendarimportController::class, 'sportsmanagementControllerjsmgcalendarImport');
}
