<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 DBB import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdbbimportController;

if (!class_exists(JlextdbbimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextdbbimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextdbbimport', false)) {
    class_alias(JlextdbbimportController::class, 'sportsmanagementControllerjlextdbbimport');
}
