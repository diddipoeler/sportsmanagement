<?php
/** Legacy compatibility bridge for the native administrator Installhelper model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\InstallhelperModel;

if (!class_exists(InstallhelperModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/InstallhelperModel.php';
}

if (!class_exists('sportsmanagementModelinstallhelper', false)) {
    class_alias(InstallhelperModel::class, 'sportsmanagementModelinstallhelper');
}
