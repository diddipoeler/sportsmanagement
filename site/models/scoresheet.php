<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/ScoresheetModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ScoresheetModel;

if (!class_exists(ScoresheetModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ScoresheetModel.php';
}

if (!class_exists('sportsmanagementModelScoresheet', false)) {
    class_alias(ScoresheetModel::class, 'sportsmanagementModelScoresheet');
}
