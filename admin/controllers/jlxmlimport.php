<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 XML import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlimportController;

if (!class_exists(JlxmlimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlxmlimportController.php';
}

if (!class_exists('sportsmanagementControllerJLXMLImport', false)) {
    class_alias(JlxmlimportController::class, 'sportsmanagementControllerJLXMLImport');
}
