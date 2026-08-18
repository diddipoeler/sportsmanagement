<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/ExtrafieldModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ExtrafieldModel;

if (!class_exists(ExtrafieldModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ExtrafieldModel.php';
}

if (!class_exists('sportsmanagementModelextrafield', false)) {
    class_alias(ExtrafieldModel::class, 'sportsmanagementModelextrafield');
}
