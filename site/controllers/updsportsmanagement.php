<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 updater controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\UpdsportsmanagementController;

if (!class_exists(UpdsportsmanagementController::class)) {
    $nativeController = JPATH_SITE
        . '/components/com_sportsmanagement/src/Controller/UpdsportsmanagementController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(UpdsportsmanagementController::class)) {
    throw new \RuntimeException('SportsManagement native Updsportsmanagement controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerUpdsportsmanagement', false)) {
    class_alias(UpdsportsmanagementController::class, 'sportsmanagementControllerUpdsportsmanagement');
}
