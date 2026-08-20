<?php
/** Legacy compatibility bridge for the native administrator Smimageimports model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportsModel;

if (!class_exists(SmimageimportsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmimageimportsModel.php';
}

if (!class_exists('sportsmanagementModelsmimageimports', false)) {
    class_alias(SmimageimportsModel::class, 'sportsmanagementModelsmimageimports');
}
