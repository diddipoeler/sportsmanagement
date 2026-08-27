<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/ResultsModel.php.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsModel;
use Diddipoeler\Component\SportsManagement\Site\Pagination\JSMSportsmanagementPagination;

if (!class_exists(ResultsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsAccessModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsEditModel.php';

    if (!class_exists(JSMSportsmanagementPagination::class)) {
        require_once JPATH_SITE . '/components/com_sportsmanagement/src/Pagination/JSMSportsmanagementPagination.php';
    }

    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsModel.php';
}

if (!class_exists('sportsmanagementModelResults', false)) {
    class_alias(ResultsModel::class, 'sportsmanagementModelResults');
}
