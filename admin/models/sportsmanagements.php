<?php
/** Legacy compatibility bridge for the native administrator Sportsmanagements list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsmanagementsModel;

if (!class_exists(SportsmanagementsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsmanagementsModel.php';
}

if (!class_exists('sportsmanagementModelsportsmanagements', false)) {
    class_alias(SportsmanagementsModel::class, 'sportsmanagementModelsportsmanagements');
}
