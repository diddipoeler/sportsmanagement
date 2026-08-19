<?php
/** Legacy compatibility bridge for the native administrator Round form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\RoundModel;

if (!class_exists(RoundModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RoundModel.php';
}

if (!class_exists('sportsmanagementModelround', false)) {
    class_alias(RoundModel::class, 'sportsmanagementModelround');
}
