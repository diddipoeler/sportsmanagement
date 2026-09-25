<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Smquote controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmquoteController;

if (!class_exists(SmquoteController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SmquoteController.php';
}

if (!class_exists(SmquoteController::class)) {
    throw new \RuntimeException('SportsManagement native Smquote controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllersmquote', false)) {
    class_alias(SmquoteController::class, 'sportsmanagementControllersmquote');
}
