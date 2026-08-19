<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Controller/RosterpositionController.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\RosterpositionController;

if (!class_exists(RosterpositionController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/RosterpositionController.php';
}

if (!class_exists('sportsmanagementControllerrosterposition', false)) {
    class_alias(RosterpositionController::class, 'sportsmanagementControllerrosterposition');
}
