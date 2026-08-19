<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Controller/DivisionController.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DivisionController;

if (!class_exists(DivisionController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/DivisionController.php';
}

if (!class_exists('sportsmanagementControllerdivision', false)) {
    class_alias(DivisionController::class, 'sportsmanagementControllerdivision');
}
