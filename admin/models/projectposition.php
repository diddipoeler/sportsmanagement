<?php
/** Legacy compatibility bridge for the native administrator Projectposition form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectpositionModel;

if (!class_exists(ProjectpositionModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectpositionModel.php';
}

if (!class_exists('sportsmanagementModelProjectposition', false)) {
    class_alias(ProjectpositionModel::class, 'sportsmanagementModelProjectposition');
}
