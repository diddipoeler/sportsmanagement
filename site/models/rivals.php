<?php
/** Legacy compatibility bridge for the native Joomla 5/6 rivals model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\RivalsModel;

if (!class_exists(RivalsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RivalsModel.php';
}

if (!class_exists('sportsmanagementModelRivals', false)) {
    class_alias(RivalsModel::class, 'sportsmanagementModelRivals');
}
