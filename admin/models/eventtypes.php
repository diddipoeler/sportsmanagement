<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Eventtypes model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\EventtypesModel;

if (!class_exists(EventtypesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/EventtypesModel.php';
}

if (!class_exists(EventtypesModel::class)) {
    throw new \RuntimeException('SportsManagement native Eventtypes model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelEventtypes', false)) {
    class_alias(EventtypesModel::class, 'sportsmanagementModelEventtypes');
}
