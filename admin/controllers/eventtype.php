<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Eventtype controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\EventtypeController;

if (!class_exists(EventtypeController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/EventtypeController.php';
}

if (!class_exists(EventtypeController::class)) {
    throw new \RuntimeException('SportsManagement native Eventtype controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllereventtype', false)) {
    class_alias(EventtypeController::class, 'sportsmanagementControllereventtype');
}
