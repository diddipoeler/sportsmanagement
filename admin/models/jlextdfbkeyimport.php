<?php
/** Legacy compatibility bridge for the native DFB-key import model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextdfbkeyimportModel;

if (!class_exists(JlextdfbkeyimportModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextdfbkeyimportModel.php';
}

if (!class_exists('sportsmanagementModeljlextDfbkeyimport', false)) {
    class_alias(JlextdfbkeyimportModel::class, 'sportsmanagementModeljlextDfbkeyimport');
}
