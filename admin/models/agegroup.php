<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 Agegroup model.
 *
 * The active implementation lives in admin/src/Model/AgegroupModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\AgegroupModel;

if (!class_exists(AgegroupModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/AgegroupModel.php';
}

if (!class_exists(AgegroupModel::class)) {
    throw new \RuntimeException('SportsManagement native Agegroup model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelagegroup', false)) {
    class_alias(AgegroupModel::class, 'sportsmanagementModelagegroup');
}
