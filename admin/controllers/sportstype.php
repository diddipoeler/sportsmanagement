<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Sportstype controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportstypeController;

if (!class_exists(SportstypeController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportstypeController.php';
}

if (!class_exists(SportstypeController::class)) {
    throw new \RuntimeException('SportsManagement native Sportstype controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllersportstype', false)) {
    class_alias(SportstypeController::class, 'sportsmanagementControllersportstype');
}
