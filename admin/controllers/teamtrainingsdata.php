<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Teamtrainingsdata controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TeamtrainingsdataController;

if (!class_exists(TeamtrainingsdataController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TeamtrainingsdataController.php';
}

if (!class_exists(TeamtrainingsdataController::class)) {
    throw new \RuntimeException('SportsManagement native Teamtrainingsdata controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerteamtrainingsdata', false)) {
    class_alias(TeamtrainingsdataController::class, 'sportsmanagementControllerteamtrainingsdata');
}
