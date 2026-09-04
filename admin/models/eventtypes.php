<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Eventtypes model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\EventtypesModel;

if (!class_exists(EventtypesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/EventtypesModel.php';
}

if (!class_exists('sportsmanagementModelEventtypes', false)) {
    class_alias(EventtypesModel::class, 'sportsmanagementModelEventtypes');
}
