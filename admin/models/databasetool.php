<?php
/** Legacy compatibility bridge for the native administrator Databasetool model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel;

if (!class_exists(DatabasetoolModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/DatabasetoolModel.php';
}

if (!class_exists('sportsmanagementModeldatabasetool', false)) {
    class_alias(DatabasetoolModel::class, 'sportsmanagementModeldatabasetool');
}
