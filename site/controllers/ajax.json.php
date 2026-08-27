<?php
/** Legacy compatibility bridge for the native Joomla 5/6 frontend Ajax controller. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\AjaxController;

if (!class_exists(AjaxController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/AjaxController.php';
}

if (!class_exists('sportsmanagementControllerAjax', false)) {
    class_alias(AjaxController::class, 'sportsmanagementControllerAjax');
}
