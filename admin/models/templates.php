<?php
/** Legacy compatibility bridge for the native administrator templates list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TemplatesModel;

if (!class_exists(TemplatesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TemplatesModel.php';
}

if (!class_exists('sportsmanagementModelTemplates', false)) {
    class_alias(TemplatesModel::class, 'sportsmanagementModelTemplates');
}
