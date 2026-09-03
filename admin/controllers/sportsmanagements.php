<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Sportsmanagements controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportsmanagementsController;

if (!class_exists(SportsmanagementsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsmanagementsController.php';
}

if (!class_exists('sportsmanagementControllersportsmanagements', false)) {
    class_alias(SportsmanagementsController::class, 'sportsmanagementControllersportsmanagements');
}
