<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 LMO import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextlmoimportsController;

if (!class_exists(JlextlmoimportsController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JlextlmoimportsController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Jlextlmoimports controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JlextlmoimportsController::class)) {
    throw new \RuntimeException('SportsManagement native Jlextlmoimports controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlextlmoimports', false)) {
    class_alias(JlextlmoimportsController::class, 'sportsmanagementControllerjlextlmoimports');
}
