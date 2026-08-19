<?php
/** Legacy compatibility bridge for the native administrator association form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextassociationModel;

if (!class_exists(JlextassociationModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextassociationModel.php';
}

if (!class_exists('sportsmanagementModeljlextassociation', false)) {
    class_alias(JlextassociationModel::class, 'sportsmanagementModeljlextassociation');
}
