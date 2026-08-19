<?php
/** Legacy compatibility bridge for the native administrator Cpanel controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\CpanelController;

if (!class_exists(CpanelController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/CpanelController.php';
}

if (!class_exists('sportsmanagementControllercpanel', false)) {
    class_alias(CpanelController::class, 'sportsmanagementControllercpanel');
}
