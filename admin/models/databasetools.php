<?php
/** Legacy compatibility bridge for the native administrator Databasetools model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolsModel;

if (!class_exists(DatabasetoolsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/DatabasetoolsModel.php';
}

if (!class_exists('sportsmanagementModelDatabaseTools', false)) {
    class_alias(DatabasetoolsModel::class, 'sportsmanagementModelDatabaseTools');
}
