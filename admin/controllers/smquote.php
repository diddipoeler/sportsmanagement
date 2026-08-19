<?php
/** Legacy compatibility bridge for the native administrator quote form controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmquoteController;

if (!class_exists(SmquoteController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SmquoteController.php';
}

if (!class_exists('sportsmanagementControllersmquote', false)) {
    class_alias(SmquoteController::class, 'sportsmanagementControllersmquote');
}
