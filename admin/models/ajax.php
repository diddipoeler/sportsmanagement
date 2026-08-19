<?php
/** Legacy compatibility bridge for the native administrator Ajax option model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\AjaxModel;

if (!class_exists(AjaxModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/AjaxModel.php';
}

if (!class_exists('sportsmanagementModelAjax', false)) {
    class_alias(AjaxModel::class, 'sportsmanagementModelAjax');
}
