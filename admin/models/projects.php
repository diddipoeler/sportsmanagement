<?php
/** Legacy compatibility bridge for the native administrator projects list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectsModel;

if (!class_exists(ProjectsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectsModel.php';
}

if (!class_exists('sportsmanagementModelProjects', false)) {
    class_alias(ProjectsModel::class, 'sportsmanagementModelProjects');
}
