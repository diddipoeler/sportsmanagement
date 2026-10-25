<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 handball.net import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlexthandballnetController;

if (!class_exists(JlexthandballnetController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JlexthandballnetController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Jlexthandballnet controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JlexthandballnetController::class)) {
    throw new \RuntimeException('SportsManagement native Jlexthandballnet controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlexthandballnet', false)) {
    class_alias(JlexthandballnetController::class, 'sportsmanagementControllerjlexthandballnet');
}
