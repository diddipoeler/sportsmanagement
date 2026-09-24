<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Leagues controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\LeaguesController;

if (!class_exists(LeaguesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/LeaguesController.php';
}

if (!class_exists(LeaguesController::class)) {
    throw new \RuntimeException('SportsManagement native Leagues controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerleagues', false)) {
    class_alias(LeaguesController::class, 'sportsmanagementControllerleagues');
}
