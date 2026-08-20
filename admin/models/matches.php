<?php
/** Legacy compatibility bridge for the native administrator matches list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchesModel;

if (!class_exists(MatchesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatchesModel.php';
}

if (!class_exists('sportsmanagementModelMatches', false)) {
    class_alias(MatchesModel::class, 'sportsmanagementModelMatches');
}
