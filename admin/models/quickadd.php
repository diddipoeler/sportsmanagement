<?php
/** Legacy compatibility bridge for the native administrator Quickadd model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\QuickaddModel;

if (!class_exists(QuickaddModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/QuickaddModel.php';
}

if (!class_exists('sportsmanagementModelQuickAdd', false)) {
    class_alias(QuickaddModel::class, 'sportsmanagementModelQuickAdd');
}
