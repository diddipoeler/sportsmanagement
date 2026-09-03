<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 LMO import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextlmoimportsController;

if (!class_exists(JlextlmoimportsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextlmoimportsController.php';
}

if (!class_exists('sportsmanagementControllerjlextlmoimports', false)) {
    class_alias(JlextlmoimportsController::class, 'sportsmanagementControllerjlextlmoimports');
}
