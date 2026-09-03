<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Treetomatch controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetomatchController;

if (!class_exists(TreetomatchController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TreetomatchController.php';
}

if (!class_exists('sportsmanagementControllerTreetomatch', false)) {
    class_alias(TreetomatchController::class, 'sportsmanagementControllerTreetomatch');
}
