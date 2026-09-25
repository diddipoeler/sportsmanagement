<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Agegroup controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\AgegroupController;

if (!class_exists(AgegroupController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/AgegroupController.php';
}

if (!class_exists(AgegroupController::class)) {
    throw new \RuntimeException('SportsManagement native Agegroup controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControlleragegroup', false)) {
    class_alias(AgegroupController::class, 'sportsmanagementControlleragegroup');
}
