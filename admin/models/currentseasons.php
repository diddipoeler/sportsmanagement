<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Currentseasons model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\CurrentseasonsModel;

if (!class_exists(CurrentseasonsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/CurrentseasonsModel.php';
}

if (!class_exists(CurrentseasonsModel::class)) {
    throw new \RuntimeException('SportsManagement native Currentseasons model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelcurrentseasons', false)) {
    class_alias(CurrentseasonsModel::class, 'sportsmanagementModelcurrentseasons');
}
