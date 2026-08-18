<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/EventtypesModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\EventtypesModel;

if (!class_exists(EventtypesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/EventtypesModel.php';
}

if (!class_exists('sportsmanagementModelEventtypes', false)) {
    class_alias(EventtypesModel::class, 'sportsmanagementModelEventtypes');
}
