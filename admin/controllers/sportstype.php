<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Controller/SportstypeController.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportstypeController;

if (!class_exists(SportstypeController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportstypeController.php';
}

if (!class_exists('sportsmanagementControllersportstype', false)) {
    class_alias(SportstypeController::class, 'sportsmanagementControllersportstype');
}
