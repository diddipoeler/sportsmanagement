<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator team players controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TeamplayersController;

if (!class_exists(TeamplayersController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TeamplayersController.php';
}

if (!class_exists('sportsmanagementControllerteamplayers', false)) {
    class_alias(TeamplayersController::class, 'sportsmanagementControllerteamplayers');
}
