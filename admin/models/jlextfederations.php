<?php
/** Legacy compatibility bridge for the native administrator federations list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextfederationsModel;

if (!class_exists(JlextfederationsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextfederationsModel.php';
}

if (!class_exists('sportsmanagementModeljlextfederations', false)) {
    class_alias(JlextfederationsModel::class, 'sportsmanagementModeljlextfederations');
}
