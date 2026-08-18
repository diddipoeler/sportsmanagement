<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/CurrentseasonsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\CurrentseasonsModel;

if (!class_exists(CurrentseasonsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/CurrentseasonsModel.php';
}

if (!class_exists('sportsmanagementModelcurrentseasons', false)) {
    class_alias(CurrentseasonsModel::class, 'sportsmanagementModelcurrentseasons');
}
