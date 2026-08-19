<?php
/** Legacy compatibility bridge for the native administrator Positionstatistic model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositionstatisticModel;

if (!class_exists(PositionstatisticModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PositionstatisticModel.php';
}

if (!class_exists('sportsmanagementModelpositionstatistic', false)) {
    class_alias(PositionstatisticModel::class, 'sportsmanagementModelpositionstatistic');
}
