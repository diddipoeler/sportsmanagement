<?php
/** SportsManagement legacy compatibility bridge for the Joomla 5/6 UEFA rating model. */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\UefawertungModel;

if (!class_exists(UefawertungModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/UefawertungModel.php';
}

if (!class_exists('sportsmanagementModeluefawertung', false)) {
    class_alias(UefawertungModel::class, 'sportsmanagementModeluefawertung');
}
