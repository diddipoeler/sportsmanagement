<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Players controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PlayersController;

if (!class_exists(PlayersController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PlayersController.php';
}

if (!class_exists(PlayersController::class)) {
    throw new \RuntimeException('SportsManagement native Players controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerplayers', false)) {
    class_alias(PlayersController::class, 'sportsmanagementControllerplayers');
}
