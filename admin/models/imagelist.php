<?php
/** Legacy compatibility bridge for the native administrator image list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ImagelistModel;

if (!class_exists(ImagelistModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ImagelistModel.php';
}

if (!class_exists('sportsmanagementModelimagelist', false)) {
    class_alias(ImagelistModel::class, 'sportsmanagementModelimagelist');
}
