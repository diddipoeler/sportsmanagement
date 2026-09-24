<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Agegroups controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\AgegroupsController;

if (!class_exists(AgegroupsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/AgegroupsController.php';
}

if (!class_exists(AgegroupsController::class)) {
    throw new \RuntimeException('SportsManagement native Agegroups controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControlleragegroups', false)) {
    class_alias(AgegroupsController::class, 'sportsmanagementControlleragegroups');
}
