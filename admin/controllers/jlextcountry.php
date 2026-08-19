<?php
/** Legacy compatibility bridge for the native administrator country controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextcountryController;

if (!class_exists(JlextcountryController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextcountryController.php';
}

if (!class_exists('sportsmanagementControllerjlextcountry', false)) {
    class_alias(JlextcountryController::class, 'sportsmanagementControllerjlextcountry');
}
