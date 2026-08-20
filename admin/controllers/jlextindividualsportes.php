<?php
/** Legacy compatibility bridge for the native administrator individual-sports controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextindividualsportesController;

if (!class_exists(JlextindividualsportesController::class)) {
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Controller/JlextindividualsportesController.php';
}

if (!class_exists('sportsmanagementControllerjlextindividualsportes', false)) {
    class_alias(JlextindividualsportesController::class, 'sportsmanagementControllerjlextindividualsportes');
}
