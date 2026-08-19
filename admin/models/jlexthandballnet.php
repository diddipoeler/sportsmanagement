<?php
/** Legacy compatibility bridge for the native handball.net import model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlexthandballnetModel;

if (!class_exists(JlexthandballnetModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlexthandballnetModel.php';
}

if (!class_exists('sportsmanagementModeljlexthandballnet', false)) {
    class_alias(JlexthandballnetModel::class, 'sportsmanagementModeljlexthandballnet');
}
