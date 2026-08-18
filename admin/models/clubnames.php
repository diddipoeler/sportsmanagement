<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/ClubnamesModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubnamesModel;

if (!class_exists(ClubnamesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ClubnamesModel.php';
}

if (!class_exists('sportsmanagementModelclubnames', false)) {
    class_alias(ClubnamesModel::class, 'sportsmanagementModelclubnames');
}
