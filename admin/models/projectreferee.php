<?php
/** Legacy compatibility bridge for the native project referee form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectrefereeModel;

if (!class_exists(ProjectrefereeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectrefereeModel.php';
}

if (!class_exists('sportsmanagementModelprojectreferee', false)) {
    class_alias(ProjectrefereeModel::class, 'sportsmanagementModelprojectreferee');
}
