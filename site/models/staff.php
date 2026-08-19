<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StaffModel;

if (!class_exists(StaffModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PersonModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/StaffModel.php';
}

if (!class_exists('sportsmanagementModelStaff', false)) {
    class_alias(StaffModel::class, 'sportsmanagementModelStaff');
}
