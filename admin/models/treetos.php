<?php
/** Legacy compatibility bridge for the native administrator treetos list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetosModel;

if (!class_exists(TreetosModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetosModel.php';
}

if (!class_exists('sportsmanagementModelTreetos', false)) {
    class_alias(TreetosModel::class, 'sportsmanagementModelTreetos');
}
