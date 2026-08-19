<?php
/** Legacy compatibility bridge for the native match staff form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstaffModel;

if (!class_exists(MatchstaffModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatchstaffModel.php';
}

if (!class_exists('sportsmanagementModelmatchstaff', false)) {
    class_alias(MatchstaffModel::class, 'sportsmanagementModelmatchstaff');
}
