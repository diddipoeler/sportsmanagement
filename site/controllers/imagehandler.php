<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 site image handler controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\ImagehandlerController;

if (!class_exists(ImagehandlerController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/ImagehandlerController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(ImagehandlerController::class)) {
    throw new \RuntimeException('SportsManagement native Imagehandler controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerImagehandler', false)) {
    class_alias(ImagehandlerController::class, 'sportsmanagementControllerImagehandler');
}
