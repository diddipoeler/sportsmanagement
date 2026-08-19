<?php
/**
 * Legacy compatibility bridge for the native Allprojects model.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllprojectsModel;

if (!class_exists(AllprojectsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllprojectsModel.php';
}

if (!class_exists('sportsmanagementModelallprojects', false)) {
    class_alias(AllprojectsModel::class, 'sportsmanagementModelallprojects');
}
