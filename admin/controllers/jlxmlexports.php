<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 XML export controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlexportsController;

if (!class_exists(JlxmlexportsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlxmlexportsController.php';
}

if (!class_exists('sportsmanagementControllerjlxmlexports', false)) {
    class_alias(JlxmlexportsController::class, 'sportsmanagementControllerjlxmlexports');
}
