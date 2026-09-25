<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Match controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\MatchController;

if (!class_exists(MatchController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/MatchController.php';
}

if (!class_exists(MatchController::class)) {
    throw new \RuntimeException('SportsManagement native Match controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllermatch', false)) {
    class_alias(MatchController::class, 'sportsmanagementControllermatch');
}
