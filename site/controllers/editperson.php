<?php
/** Legacy compatibility bridge for the native Joomla 5/6 edit-person controller. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\EditpersonController;

if (!class_exists(EditpersonController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EditpersonController.php';
}

if (!class_exists('sportsmanagementControllereditperson', false)) {
    class_alias(EditpersonController::class, 'sportsmanagementControllereditperson');
}
