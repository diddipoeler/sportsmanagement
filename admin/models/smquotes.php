<?php
/** Legacy compatibility bridge for the native administrator quotes list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotesModel;

if (!class_exists(SmquotesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmquotesModel.php';
}

if (!class_exists('sportsmanagementModelsmquotes', false)) {
    class_alias(SmquotesModel::class, 'sportsmanagementModelsmquotes');
}
