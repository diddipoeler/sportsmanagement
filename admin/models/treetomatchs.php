<?php
/** Legacy compatibility bridge for the native tournament-tree matches list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetomatchsModel;

if (!class_exists(TreetomatchsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetomatchsModel.php';
}

if (!class_exists('sportsmanagementModelTreetomatchs', false)) {
    class_alias(TreetomatchsModel::class, 'sportsmanagementModelTreetomatchs');
}
