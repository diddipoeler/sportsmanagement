<?php
/** Legacy compatibility bridge for the native frontend Editclub controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\EditclubController;

if (!class_exists(EditclubController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EditclubController.php';
}

if (!class_exists('sportsmanagementControllerEditClub', false)) {
    class_alias(EditclubController::class, 'sportsmanagementControllerEditClub');
}
