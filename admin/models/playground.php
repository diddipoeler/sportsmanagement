<?php
/** Legacy compatibility bridge for the native administrator Playground model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundModel;

if (!class_exists(PlaygroundModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlaygroundModel.php';
}

if (!class_exists('sportsmanagementModelPlayground', false)) {
    class_alias(PlaygroundModel::class, 'sportsmanagementModelPlayground');
}
