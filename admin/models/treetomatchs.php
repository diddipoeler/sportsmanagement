<?php
/**
 * Legacy compatibility bridge for the native tournament-tree matches list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetomatchsModel;

if (!class_exists(TreetomatchsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetomatchsModel.php';
}

if (!class_exists('sportsmanagementModelTreetomatchs', false)) {
    class_alias(TreetomatchsModel::class, 'sportsmanagementModelTreetomatchs');
}
