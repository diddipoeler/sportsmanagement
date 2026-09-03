<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 team training-data list controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TeamtrainingsdatasController;

if (!class_exists(TeamtrainingsdatasController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TeamtrainingsdatasController.php';
}

if (!class_exists('sportsmanagementControllerteamtrainingsdatas', false)) {
    class_alias(TeamtrainingsdatasController::class, 'sportsmanagementControllerteamtrainingsdatas');
}
