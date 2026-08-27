<?php
/** Legacy compatibility bridge for the native Joomla 5/6 frontend Ajax model. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\AjaxModel;

if (!class_exists(AjaxModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AjaxModel.php';
}

if (!class_exists('sportsmanagementModelAjax', false)) {
    class_alias(AjaxModel::class, 'sportsmanagementModelAjax');
}
