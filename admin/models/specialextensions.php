<?php
/** Legacy compatibility bridge for the native administrator Specialextensions model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SpecialextensionsModel;

if (!class_exists(SpecialextensionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SpecialextensionsModel.php';
}

if (!class_exists('sportsmanagementModelspecialextensions', false)) {
    class_alias(SpecialextensionsModel::class, 'sportsmanagementModelspecialextensions');
}
