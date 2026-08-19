<?php
/** Legacy compatibility bridge for the native tournament-tree nodes list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodesModel;

if (!class_exists(TreetonodesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetonodesModel.php';
}

if (!class_exists('sportsmanagementModelTreetonodes', false)) {
    class_alias(TreetonodesModel::class, 'sportsmanagementModelTreetonodes');
}
