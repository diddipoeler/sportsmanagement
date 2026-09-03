<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Update controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\UpdateController;

if (!class_exists(UpdateController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/UpdateController.php';
}

if (!class_exists('sportsmanagementControllerUpdate', false)) {
    class_alias(UpdateController::class, 'sportsmanagementControllerUpdate');
}
