<?php
/** Legacy compatibility bridge for the native administrator image handler model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ImagehandlerModel;

if (!class_exists(ImagehandlerModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ImagehandlerModel.php';
}

if (!class_exists('sportsmanagementModelImagehandler', false)) {
    class_alias(ImagehandlerModel::class, 'sportsmanagementModelImagehandler');
}
