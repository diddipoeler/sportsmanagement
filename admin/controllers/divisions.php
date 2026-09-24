<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Divisions controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DivisionsController;

if (!class_exists(DivisionsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/DivisionsController.php';
}

if (!class_exists(DivisionsController::class)) {
    throw new \RuntimeException('SportsManagement native Divisions controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerdivisions', false)) {
    class_alias(DivisionsController::class, 'sportsmanagementControllerdivisions');
}
