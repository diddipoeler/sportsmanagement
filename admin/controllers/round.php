<?php
/** Legacy compatibility bridge for the native administrator round form controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\RoundController;

if (!class_exists(RoundController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/RoundController.php';
}

if (!class_exists('sportsmanagementControllerround', false)) {
    class_alias(RoundController::class, 'sportsmanagementControllerround');
}
