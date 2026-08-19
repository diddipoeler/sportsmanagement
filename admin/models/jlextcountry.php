<?php
/** Legacy compatibility bridge for the native administrator country form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextcountryModel;

if (!class_exists(JlextcountryModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextcountryModel.php';
}

if (!class_exists('sportsmanagementModeljlextcountry', false)) {
    class_alias(JlextcountryModel::class, 'sportsmanagementModeljlextcountry');
}
