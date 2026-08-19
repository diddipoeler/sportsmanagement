<?php
/** Legacy compatibility bridge for the native administrator quotes list controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmquotesController;

if (!class_exists(SmquotesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SmquotesController.php';
}

if (!class_exists('sportsmanagementControllersmquotes', false)) {
    class_alias(SmquotesController::class, 'sportsmanagementControllersmquotes');
}
