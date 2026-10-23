<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 XML export controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlexportsController;

if (!class_exists(JlxmlexportsController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JlxmlexportsController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native XML export controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JlxmlexportsController::class)) {
    throw new \RuntimeException('SportsManagement native XML export controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlxmlexports', false)) {
    class_alias(JlxmlexportsController::class, 'sportsmanagementControllerjlxmlexports');
}
