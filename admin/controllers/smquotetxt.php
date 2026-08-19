<?php
/** Legacy compatibility bridge for the native administrator quote text editor controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmquotetxtController;

if (!class_exists(SmquotetxtController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SmquotetxtController.php';
}

if (!class_exists('sportsmanagementControllersmquotetxt', false)) {
    class_alias(SmquotetxtController::class, 'sportsmanagementControllersmquotetxt');
}
