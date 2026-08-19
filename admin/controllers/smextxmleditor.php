<?php
/** Legacy compatibility bridge for the native extended XML editor controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmextxmleditorController;

if (!class_exists(SmextxmleditorController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SmextxmleditorController.php';
}

if (!class_exists('sportsmanagementControllersmextxmleditor', false)) {
    class_alias(SmextxmleditorController::class, 'sportsmanagementControllersmextxmleditor');
}
