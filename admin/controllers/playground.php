<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Playground controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PlaygroundController;

if (!class_exists(PlaygroundController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PlaygroundController.php';
}

if (!class_exists('sportsmanagementControllerplayground', false)) {
    class_alias(PlaygroundController::class, 'sportsmanagementControllerplayground');
}
