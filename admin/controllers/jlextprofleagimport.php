<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 ProfiLeague import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextprofleagimportController;

if (!class_exists(JlextprofleagimportController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JlextprofleagimportController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Jlextprofleagimport controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JlextprofleagimportController::class)) {
    throw new \RuntimeException('SportsManagement native Jlextprofleagimport controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlextprofleagimport', false)) {
    class_alias(JlextprofleagimportController::class, 'sportsmanagementControllerjlextprofleagimport');
}
