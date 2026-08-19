<?php
/**
 * Legacy compatibility bridge for the native administrator Sportstypes model.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportstypesModel;

if (!class_exists(SportstypesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportstypesModel.php';
}

if (!class_exists('sportsmanagementModelSportsTypes', false)) {
    class_alias(SportstypesModel::class, 'sportsmanagementModelSportsTypes');
}
