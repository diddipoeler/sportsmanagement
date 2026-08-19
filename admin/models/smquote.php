<?php
/** Legacy compatibility bridge for the native administrator quote form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmquoteModel;

if (!class_exists(SmquoteModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmquoteModel.php';
}

if (!class_exists('sportsmanagementModelsmquote', false)) {
    class_alias(SmquoteModel::class, 'sportsmanagementModelsmquote');
}
