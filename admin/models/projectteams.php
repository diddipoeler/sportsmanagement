<?php
/** Legacy compatibility bridge for the native administrator project teams list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel;

if (!class_exists(ProjectteamsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/ProjectRelationService.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectteamsModel.php';
}

if (!class_exists('sportsmanagementModelProjectteams', false)) {
    class_alias(ProjectteamsModel::class, 'sportsmanagementModelProjectteams');
}
