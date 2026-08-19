<?php
/** Legacy compatibility bridge for the native extended XML editors controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmextxmleditorsController;

if (!class_exists(SmextxmleditorsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SmextxmleditorsController.php';
}

if (!class_exists('sportsmanagementControllersmextxmleditors', false)) {
    class_alias(SmextxmleditorsController::class, 'sportsmanagementControllersmextxmleditors');
}
