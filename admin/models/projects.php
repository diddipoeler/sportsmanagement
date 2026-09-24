<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Projects model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectsModel;

if (!class_exists(ProjectsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectsModel.php';
}

if (!class_exists(ProjectsModel::class)) {
    throw new \RuntimeException('SportsManagement native Projects model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProjects', false)) {
    class_alias(ProjectsModel::class, 'sportsmanagementModelProjects');
}
