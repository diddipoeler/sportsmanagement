<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Project model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel;

if (!class_exists(ProjectModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectModel.php';
}

if (!class_exists(ProjectModel::class)) {
    throw new \RuntimeException('SportsManagement native Project model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProject', false)) {
    class_alias(ProjectModel::class, 'sportsmanagementModelProject');
}
