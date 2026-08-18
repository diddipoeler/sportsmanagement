<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/RosterpositionModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\RosterpositionModel;

if (!class_exists(RosterpositionModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RosterpositionModel.php';
}

if (!class_exists('sportsmanagementModelrosterposition', false)) {
    class_alias(RosterpositionModel::class, 'sportsmanagementModelrosterposition');
}
