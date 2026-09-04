<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Ajax controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\AjaxController;

if (!class_exists(AjaxController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/AjaxController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(AjaxController::class)) {
    throw new \RuntimeException('SportsManagement native Ajax controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerAjax', false)) {
    class_alias(AjaxController::class, 'sportsmanagementControllerAjax');
}
