<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Leagues controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\LeaguesController;

if (!class_exists(LeaguesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/LeaguesController.php';
}

if (!class_exists('sportsmanagementControllerleagues', false)) {
    class_alias(LeaguesController::class, 'sportsmanagementControllerleagues');
}
