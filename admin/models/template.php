<?php
/** Legacy compatibility bridge for the native administrator template model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TemplateModel;

if (!class_exists(TemplateModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TemplateModel.php';
}

if (!class_exists('sportsmanagementModeltemplate', false)) {
    class_alias(TemplateModel::class, 'sportsmanagementModeltemplate');
}
