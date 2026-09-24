<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Extrafield controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ExtrafieldController;

if (!class_exists(ExtrafieldController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ExtrafieldController.php';
}

if (!class_exists(ExtrafieldController::class)) {
    throw new \RuntimeException('SportsManagement native Extrafield controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerextrafield', false)) {
    class_alias(ExtrafieldController::class, 'sportsmanagementControllerextrafield');
}
