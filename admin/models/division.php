<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/DivisionModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\DivisionModel;

if (!class_exists(DivisionModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/DivisionModel.php';
}

if (!class_exists('sportsmanagementModeldivision', false)) {
    class_alias(DivisionModel::class, 'sportsmanagementModeldivision');
}
