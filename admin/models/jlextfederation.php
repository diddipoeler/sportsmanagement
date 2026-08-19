<?php
/** Legacy compatibility bridge for the native administrator federation form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextfederationModel;

if (!class_exists(JlextfederationModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextfederationModel.php';
}

if (!class_exists('sportsmanagementModeljlextfederation', false)) {
    class_alias(JlextfederationModel::class, 'sportsmanagementModeljlextfederation');
}
