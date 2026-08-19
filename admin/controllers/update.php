<?php
/** Legacy compatibility bridge for the native administrator Update controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\UpdateController;

if (!class_exists(UpdateController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/UpdateController.php';
}

if (!class_exists('sportsmanagementControllerUpdate', false)) {
    class_alias(UpdateController::class, 'sportsmanagementControllerUpdate');
}
