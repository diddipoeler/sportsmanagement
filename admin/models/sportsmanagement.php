<?php
/** Legacy compatibility bridge for the native administrator Sportsmanagement form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsmanagementModel;

if (!class_exists(SportsmanagementModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsmanagementModel.php';
}

if (!class_exists('sportsmanagementModelsportsmanagement', false)) {
    class_alias(SportsmanagementModel::class, 'sportsmanagementModelsportsmanagement');
}
