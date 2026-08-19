<?php
/** Legacy compatibility bridge for the native match referee form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchrefereeModel;

if (!class_exists(MatchrefereeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatchrefereeModel.php';
}

if (!class_exists('sportsmanagementModelmatchreferee', false)) {
    class_alias(MatchrefereeModel::class, 'sportsmanagementModelmatchreferee');
}
