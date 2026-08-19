<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Controller/ExtrafieldController.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ExtrafieldController;

if (!class_exists(ExtrafieldController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ExtrafieldController.php';
}

if (!class_exists('sportsmanagementControllerextrafield', false)) {
    class_alias(ExtrafieldController::class, 'sportsmanagementControllerextrafield');
}
