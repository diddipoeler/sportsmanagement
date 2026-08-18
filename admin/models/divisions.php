<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/DivisionsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\DivisionsModel;

if (!class_exists(DivisionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/DivisionsModel.php';
}

if (!class_exists('sportsmanagementModelDivisions', false)) {
    class_alias(DivisionsModel::class, 'sportsmanagementModelDivisions');
}
