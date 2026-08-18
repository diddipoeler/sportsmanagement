<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/SportstypeModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportstypeModel;

if (!class_exists(SportstypeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportstypeModel.php';
}

if (!class_exists('sportsmanagementModelsportstype', false)) {
    class_alias(SportstypeModel::class, 'sportsmanagementModelsportstype');
}
