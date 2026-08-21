<?php
/** Legacy compatibility bridge for the native site image handler controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\ImagehandlerController;

if (!class_exists(ImagehandlerController::class)) {
    $controllerFile = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/ImagehandlerController.php';

    if (is_file($controllerFile)) {
        require_once $controllerFile;
    }
}

if (class_exists(ImagehandlerController::class) && !class_exists('sportsmanagementControllerImagehandler', false)) {
    class_alias(ImagehandlerController::class, 'sportsmanagementControllerImagehandler');
}
