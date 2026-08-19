<?php
/** Legacy compatibility bridge for the native administrator countries list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextcountriesModel;

if (!class_exists(JlextcountriesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextcountriesModel.php';
}

if (!class_exists('sportsmanagementModeljlextcountries', false)) {
    class_alias(JlextcountriesModel::class, 'sportsmanagementModeljlextcountries');
}
