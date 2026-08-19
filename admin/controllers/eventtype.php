<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Controller/EventtypeController.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\EventtypeController;

if (!class_exists(EventtypeController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/EventtypeController.php';
}

if (!class_exists('sportsmanagementControllereventtype', false)) {
    class_alias(EventtypeController::class, 'sportsmanagementControllereventtype');
}
