<?php
/** Legacy compatibility bridge for the native administrator treeto model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetoModel;

if (!class_exists(TreetoModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetoModel.php';
}

if (!class_exists('sportsmanagementModelTreeto', false)) {
    class_alias(TreetoModel::class, 'sportsmanagementModelTreeto');
}
