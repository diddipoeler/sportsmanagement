<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Specialextensions model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SpecialextensionsModel;

if (!class_exists(SpecialextensionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SpecialextensionsModel.php';
}

if (!class_exists(SpecialextensionsModel::class)) {
    throw new \RuntimeException('SportsManagement native Specialextensions model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelspecialextensions', false)) {
    class_alias(SpecialextensionsModel::class, 'sportsmanagementModelspecialextensions');
}
