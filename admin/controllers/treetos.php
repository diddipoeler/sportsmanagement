<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Treetos controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetosController;

if (!class_exists(TreetosController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TreetosController.php';
}

if (!class_exists('sportsmanagementControllertreetos', false)) {
    class_alias(TreetosController::class, 'sportsmanagementControllertreetos');
}
