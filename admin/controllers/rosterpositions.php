<?php
/** Legacy compatibility bridge for the native administrator Rosterpositions controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\RosterpositionsController;

if (!class_exists(RosterpositionsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/RosterpositionsController.php';
}

if (!class_exists('sportsmanagementControllerrosterpositions', false)) {
    class_alias(RosterpositionsController::class, 'sportsmanagementControllerrosterpositions');
}
