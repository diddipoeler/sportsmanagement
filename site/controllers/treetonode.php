<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 tree-to-node controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\TreetonodeController;

if (!class_exists(TreetonodeController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/TreetonodeController.php';
}

if (!class_exists('sportsmanagementControllerTreetonode', false)) {
    class_alias(TreetonodeController::class, 'sportsmanagementControllerTreetonode');
}
