<?php
/** Legacy compatibility bridge for the native Joomla 5/6 edit-match controller. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\EditmatchController;

if (!class_exists(EditmatchController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EditmatchController.php';
}

if (!class_exists('sportsmanagementControllerEditmatch', false)) {
    class_alias(EditmatchController::class, 'sportsmanagementControllerEditmatch');
}
