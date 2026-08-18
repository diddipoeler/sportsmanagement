<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/PlaygroundsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundsModel;

if (!class_exists(PlaygroundsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlaygroundsModel.php';
}

if (!class_exists('sportsmanagementModelPlaygrounds', false)) {
    class_alias(PlaygroundsModel::class, 'sportsmanagementModelPlaygrounds');
}
