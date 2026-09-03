<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Installhelper controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\InstallhelperController;

if (!class_exists(InstallhelperController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/InstallhelperController.php';
}

if (!class_exists('sportsmanagementControllerinstallhelper', false)) {
    class_alias(InstallhelperController::class, 'sportsmanagementControllerinstallhelper');
}
