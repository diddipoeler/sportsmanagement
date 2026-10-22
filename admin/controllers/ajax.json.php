<?php
/**
 * Legacy JSON compatibility bridge for the native Joomla 5/6 administrator Ajax controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\AjaxController;

if (!class_exists(AjaxController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/AjaxController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native administrator Ajax controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(AjaxController::class)) {
    throw new \RuntimeException('SportsManagement native administrator Ajax controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerAjax', false)) {
    class_alias(AjaxController::class, 'sportsmanagementControllerAjax');
}
