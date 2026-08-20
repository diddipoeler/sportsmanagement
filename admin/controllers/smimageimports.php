<?php
/** Legacy compatibility bridge for the native administrator Smimageimports controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmimageimportsController;

if (!class_exists(SmimageimportsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SmimageimportsController.php';
}

if (!class_exists('sportsmanagementControllersmimageimports', false)) {
    class_alias(SmimageimportsController::class, 'sportsmanagementControllersmimageimports');
}
