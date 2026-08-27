<?php
/** Legacy compatibility bridge for the native Joomla 5/6 results controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\ResultsController;

if (!class_exists(ResultsController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/ResultsController.php';
}

if (!class_exists('sportsmanagementControllerResults', false)) {
    class_alias(ResultsController::class, 'sportsmanagementControllerResults');
}
