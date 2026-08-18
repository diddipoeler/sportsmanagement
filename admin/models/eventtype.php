<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/EventtypeModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\EventtypeModel;

if (!class_exists(EventtypeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/EventtypeModel.php';
}

if (!class_exists('sportsmanagementModeleventtype', false)) {
    class_alias(EventtypeModel::class, 'sportsmanagementModeleventtype');
}
