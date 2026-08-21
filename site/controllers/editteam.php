<?php
/** Legacy compatibility bridge for the native frontend Editteam controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\EditteamController;

if (!class_exists(EditteamController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EditteamController.php';
}

if (!class_exists('sportsmanagementControllereditteam', false)) {
    class_alias(EditteamController::class, 'sportsmanagementControllereditteam');
}
