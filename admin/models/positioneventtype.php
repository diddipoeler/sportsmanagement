<?php
/** Legacy compatibility bridge for the native administrator Positioneventtype model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositioneventtypeModel;

if (!class_exists(PositioneventtypeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PositioneventtypeModel.php';
}

if (!class_exists('sportsmanagementModelpositioneventtype', false)) {
    class_alias(PositioneventtypeModel::class, 'sportsmanagementModelpositioneventtype');
}
