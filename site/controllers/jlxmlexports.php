<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JL XML exports controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\JlxmlexportsController;

if (!class_exists(JlxmlexportsController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/JlxmlexportsController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(JlxmlexportsController::class)) {
    throw new \RuntimeException('SportsManagement native Jlxmlexports controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlxmlexports', false)) {
    class_alias(JlxmlexportsController::class, 'sportsmanagementControllerjlxmlexports');
}
