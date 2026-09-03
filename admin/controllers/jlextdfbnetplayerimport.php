<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 DFB.net import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdfbnetplayerimportController;

if (!class_exists(JlextdfbnetplayerimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextdfbnetplayerimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextdfbnetplayerimport', false)) {
    class_alias(JlextdfbnetplayerimportController::class, 'sportsmanagementControllerjlextdfbnetplayerimport');
}
