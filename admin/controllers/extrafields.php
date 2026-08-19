<?php
/** Legacy compatibility bridge for the native administrator Extrafields controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ExtrafieldsController;

if (!class_exists(ExtrafieldsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ExtrafieldsController.php';
}

if (!class_exists('sportsmanagementControllerextrafields', false)) {
    class_alias(ExtrafieldsController::class, 'sportsmanagementControllerextrafields');
}
