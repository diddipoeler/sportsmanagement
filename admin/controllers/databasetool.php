<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Databasetool controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DatabasetoolController;

if (!class_exists(DatabasetoolController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/DatabasetoolController.php';
}

if (!class_exists('sportsmanagementControllerDatabaseTool', false)) {
    class_alias(DatabasetoolController::class, 'sportsmanagementControllerDatabaseTool');
}
