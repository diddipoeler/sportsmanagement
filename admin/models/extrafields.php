<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/ExtrafieldsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ExtrafieldsModel;

if (!class_exists(ExtrafieldsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ExtrafieldsModel.php';
}

if (!class_exists('sportsmanagementModelextrafields', false)) {
    class_alias(ExtrafieldsModel::class, 'sportsmanagementModelextrafields');
}
