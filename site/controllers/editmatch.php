<?php
/** Legacy compatibility bridge for the native frontend Editmatch controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\EditmatchController;

if (!class_exists(EditmatchController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EditmatchController.php';
}

if (!class_exists('sportsmanagementControllerEditMatch', false)) {
    class_alias(EditmatchController::class, 'sportsmanagementControllerEditMatch');
}
