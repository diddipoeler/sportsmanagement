<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/ResultsrankingModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingModel;

if (!class_exists(ResultsrankingModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsrankingModel.php';
}

if (!class_exists('sportsmanagementModelResultsranking', false)) {
    class_alias(ResultsrankingModel::class, 'sportsmanagementModelResultsranking');
}
