<?php
/** Legacy compatibility bridge for the native Joomla 5/6 JL XML exports controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\JlxmlexportsController;

if (!class_exists(JlxmlexportsController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/JlxmlexportsController.php';
}

if (!class_exists('sportsmanagementControllerjlxmlexports', false)) {
    class_alias(JlxmlexportsController::class, 'sportsmanagementControllerjlxmlexports');
}
