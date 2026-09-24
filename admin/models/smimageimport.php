<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Smimageimport model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportModel;

if (!class_exists(SmimageimportModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmimageimportModel.php';
}

if (!class_exists(SmimageimportModel::class)) {
    throw new \RuntimeException('SportsManagement native Smimageimport model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsmimageimport', false)) {
    class_alias(SmimageimportModel::class, 'sportsmanagementModelsmimageimport');
}
