<?php
/** Legacy compatibility bridge for the native administrator Smimageimport model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportModel;

if (!class_exists(SmimageimportModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmimageimportModel.php';
}

if (!class_exists('sportsmanagementModelsmimageimport', false)) {
    class_alias(SmimageimportModel::class, 'sportsmanagementModelsmimageimport');
}
