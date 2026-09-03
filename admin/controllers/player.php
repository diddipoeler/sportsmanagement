<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Player controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PlayerController;

if (!class_exists(PlayerController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PlayerController.php';
}

if (!class_exists('sportsmanagementControllerplayer', false)) {
    class_alias(PlayerController::class, 'sportsmanagementControllerplayer');
}
