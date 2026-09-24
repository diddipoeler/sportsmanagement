<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator League controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\LeagueController;

if (!class_exists(LeagueController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/LeagueController.php';
}

if (!class_exists(LeagueController::class)) {
    throw new \RuntimeException('SportsManagement native League controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerleague', false)) {
    class_alias(LeagueController::class, 'sportsmanagementControllerleague');
}
