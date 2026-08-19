<?php
/** Legacy compatibility bridge for the native administrator Updates model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\UpdatesModel;

if (!class_exists(UpdatesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/UpdatesModel.php';
}

if (!class_exists('sportsmanagementModelUpdates', false)) {
    class_alias(UpdatesModel::class, 'sportsmanagementModelUpdates');
}
