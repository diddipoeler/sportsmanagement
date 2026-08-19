<?php
/** Legacy compatibility bridge for the native administrator Seasonteamperson form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonteampersonModel;

if (!class_exists(SeasonteampersonModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SeasonteampersonModel.php';
}

if (!class_exists('sportsmanagementModelseasonteamperson', false)) {
    class_alias(SeasonteampersonModel::class, 'sportsmanagementModelseasonteamperson');
}
