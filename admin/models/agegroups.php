<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/AgegroupsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\AgegroupsModel;

if (!class_exists(AgegroupsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/AgegroupsModel.php';
}

if (!class_exists('sportsmanagementModelagegroups', false)) {
    class_alias(AgegroupsModel::class, 'sportsmanagementModelagegroups');
}
