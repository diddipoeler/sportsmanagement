<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Eventtype model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\EventtypeModel;

if (!class_exists(EventtypeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/EventtypeModel.php';
}

if (!class_exists('sportsmanagementModeleventtype', false)) {
    class_alias(EventtypeModel::class, 'sportsmanagementModeleventtype');
}
