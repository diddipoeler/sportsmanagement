<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Projectteam controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectteamController;

if (!class_exists(ProjectteamController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ProjectteamController.php';
}

if (!class_exists(ProjectteamController::class)) {
    throw new \RuntimeException('SportsManagement native Projectteam controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerprojectteam', false)) {
    class_alias(ProjectteamController::class, 'sportsmanagementControllerprojectteam');
}
