<?php
/**
 * Results-view compatibility adapter for the legacy global Round model name.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;

if (!class_exists(ResultsDataModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php';
}

if (!class_exists('sportsmanagementModelRound', false)) {
    final class sportsmanagementModelRound
    {
        public static function getRoundcode($roundId = 0, $cfgWhichDatabase = 0)
        {
            $model = new ResultsDataModel();
            $model->setDatabaseSelector((int) $cfgWhichDatabase);

            return $model->getRoundCode((int) $roundId);
        }
    }
}
