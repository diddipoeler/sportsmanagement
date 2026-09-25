<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Sportsmanagement controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportsmanagementController;

if (!class_exists(SportsmanagementController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsmanagementController.php';
}

if (!class_exists(SportsmanagementController::class)) {
    throw new \RuntimeException('SportsManagement native Sportsmanagement controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllersportsmanagement', false)) {
    class_alias(SportsmanagementController::class, 'sportsmanagementControllersportsmanagement');
}
