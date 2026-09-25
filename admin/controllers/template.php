<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Template controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TemplateController;

if (!class_exists(TemplateController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TemplateController.php';
}

if (!class_exists(TemplateController::class)) {
    throw new \RuntimeException('SportsManagement native Template controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllertemplate', false)) {
    class_alias(TemplateController::class, 'sportsmanagementControllertemplate');
}
