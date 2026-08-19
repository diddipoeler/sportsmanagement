<?php
/** Legacy compatibility bridge for the native project referees list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectrefereesModel;

if (!class_exists(ProjectrefereesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectrefereesModel.php';
}

if (!class_exists('sportsmanagementModelProjectReferees', false)) {
    class_alias(ProjectrefereesModel::class, 'sportsmanagementModelProjectReferees');
}
