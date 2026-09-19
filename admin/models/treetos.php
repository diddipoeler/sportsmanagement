<?php
/**
 * Legacy compatibility bridge for the native administrator treetos list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetosModel;

if (!class_exists(TreetosModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetosModel.php';
}

if (!class_exists(TreetosModel::class)) {
    throw new \RuntimeException('SportsManagement native Treetos model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTreetos', false)) {
    class_alias(TreetosModel::class, 'sportsmanagementModelTreetos');
}
