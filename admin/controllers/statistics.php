<?php
/** Legacy compatibility bridge for the native Joomla 5/6 statistics list controller. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\StatisticsController;

if (!class_exists(StatisticsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/StatisticsController.php';
}

if (!class_exists('sportsmanagementControllerstatistics', false)) {
    class_alias(StatisticsController::class, 'sportsmanagementControllerstatistics');
}
