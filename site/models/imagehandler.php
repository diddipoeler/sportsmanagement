<?php
/** Legacy compatibility bridge for the native site image handler model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ImagehandlerModel;

if (!class_exists(ImagehandlerModel::class)) {
    $modelFile = JPATH_SITE . '/components/com_sportsmanagement/src/Model/ImagehandlerModel.php';

    if (is_file($modelFile)) {
        require_once $modelFile;
    }
}

if (class_exists(ImagehandlerModel::class) && !class_exists('sportsmanagementModelImagehandler', false)) {
    class_alias(ImagehandlerModel::class, 'sportsmanagementModelImagehandler');
}
