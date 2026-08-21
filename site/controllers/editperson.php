<?php
/** Legacy compatibility bridge for the native frontend Editperson controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\EditpersonController;

if (!class_exists(EditpersonController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EditpersonController.php';
}

if (!class_exists('sportsmanagementControllereditperson', false)) {
    class_alias(EditpersonController::class, 'sportsmanagementControllereditperson');
}
