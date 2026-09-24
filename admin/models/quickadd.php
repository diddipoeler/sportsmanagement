<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Quickadd model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\QuickaddModel;

if (!class_exists(QuickaddModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/QuickaddModel.php';
}

if (!class_exists(QuickaddModel::class)) {
    throw new \RuntimeException('SportsManagement native Quickadd model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelQuickAdd', false)) {
    class_alias(QuickaddModel::class, 'sportsmanagementModelQuickAdd');
}
