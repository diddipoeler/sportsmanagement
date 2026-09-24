<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Division controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DivisionController;

if (!class_exists(DivisionController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/DivisionController.php';
}

if (!class_exists(DivisionController::class)) {
    throw new \RuntimeException('SportsManagement native Division controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerdivision', false)) {
    class_alias(DivisionController::class, 'sportsmanagementControllerdivision');
}
