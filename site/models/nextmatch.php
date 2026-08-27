<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/NextmatchModel.php.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\NextmatchModel;

if (!class_exists(NextmatchModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Legacy/LegacyBootstrap.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/NextmatchModel.php';
}

if (!class_exists('sportsmanagementModelNextMatch', false)) {
    class_alias(NextmatchModel::class, 'sportsmanagementModelNextMatch');
}
