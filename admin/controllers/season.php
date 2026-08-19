<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Controller/SeasonController.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SeasonController;

if (!class_exists(SeasonController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SeasonController.php';
}

if (!class_exists('sportsmanagementControllerseason', false)) {
    class_alias(SeasonController::class, 'sportsmanagementControllerseason');
}
