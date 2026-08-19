<?php
/** Legacy compatibility bridge for the native administrator Seasonperson form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonpersonModel;

if (!class_exists(SeasonpersonModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SeasonpersonModel.php';
}

if (!class_exists('sportsmanagementModelseasonperson', false)) {
    class_alias(SeasonpersonModel::class, 'sportsmanagementModelseasonperson');
}
