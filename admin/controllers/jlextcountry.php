<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextcountry controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextcountryController;

if (!class_exists(JlextcountryController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextcountryController.php';
}

if (!class_exists(JlextcountryController::class)) {
    throw new \RuntimeException('SportsManagement native Jlextcountry controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlextcountry', false)) {
    class_alias(JlextcountryController::class, 'sportsmanagementControllerjlextcountry');
}
