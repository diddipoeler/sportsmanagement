<?php
/**
 * Legacy administrator controller bridge for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DisplayController;

if (!class_exists(DisplayController::class)) {
    $nativeController = __DIR__ . '/src/Controller/DisplayController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(DisplayController::class)) {
    throw new \RuntimeException('SportsManagement native administrator controller could not be loaded.', 500);
}

if (!class_exists('SportsManagementController', false)) {
    class_alias(DisplayController::class, 'SportsManagementController');
}
