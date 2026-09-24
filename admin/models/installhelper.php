<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Installhelper model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\InstallhelperModel;

if (!class_exists(InstallhelperModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/InstallhelperModel.php';
}

if (!class_exists(InstallhelperModel::class)) {
    throw new \RuntimeException('SportsManagement native Installhelper model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelinstallhelper', false)) {
    class_alias(InstallhelperModel::class, 'sportsmanagementModelinstallhelper');
}
