<?php
/** Legacy compatibility bridge for the native administrator Projectpositions list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectpositionsModel;

if (!class_exists(ProjectpositionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectpositionsModel.php';
}

if (!class_exists('sportsmanagementModelProjectpositions', false)) {
    class_alias(ProjectpositionsModel::class, 'sportsmanagementModelProjectpositions');
}
