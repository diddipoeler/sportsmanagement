<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/SeasonModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonModel;

if (!class_exists(SeasonModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SeasonModel.php';
}

if (!class_exists('sportsmanagementModelseason', false)) {
    class_alias(SeasonModel::class, 'sportsmanagementModelseason');
}
