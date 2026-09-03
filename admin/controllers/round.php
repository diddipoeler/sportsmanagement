<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Round form controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\RoundController;

if (!class_exists(RoundController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/RoundController.php';
}

if (!class_exists('sportsmanagementControllerround', false)) {
    class_alias(RoundController::class, 'sportsmanagementControllerround');
}
