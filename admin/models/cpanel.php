<?php
/** Legacy compatibility bridge for the native administrator Cpanel model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\CpanelModel;

if (!class_exists(CpanelModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/CpanelModel.php';
}

if (!class_exists('sportsmanagementModelcpanel', false)) {
    class_alias(CpanelModel::class, 'sportsmanagementModelcpanel');
}
