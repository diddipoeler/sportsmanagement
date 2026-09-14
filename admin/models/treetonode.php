<?php
/**
 * Legacy compatibility bridge for the native tournament-tree node model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodeModel;

if (!class_exists(TreetonodeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetonodeModel.php';
}

if (!class_exists('sportsmanagementModelTreetonode', false)) {
    class_alias(TreetonodeModel::class, 'sportsmanagementModelTreetonode');
}
