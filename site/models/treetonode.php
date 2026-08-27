<?php
/** SportsManagement legacy compatibility bridge for the Joomla 5/6 tree-to-node model. */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TreetonodeModel;

if (!class_exists(TreetonodeModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TreetonodeModel.php';
}

if (!class_exists('sportsmanagementModelTreetonode', false)) {
    class_alias(TreetonodeModel::class, 'sportsmanagementModelTreetonode');
}
