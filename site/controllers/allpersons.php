<?php
/** Legacy compatibility bridge for the native Joomla 5/6 all persons controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\AllpersonsController;

if (!class_exists(AllpersonsController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/AllpersonsController.php';
}

if (!class_exists('sportsmanagementControllerallpersons', false)) {
    class_alias(AllpersonsController::class, 'sportsmanagementControllerallpersons');
}
