<?php
/**
 * Results-view compatibility adapter for the one legacy Match helper still
 * used by the frontend results form.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

if (!class_exists(ResultsDataModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php';
}

if (!class_exists('sportsmanagementModelMatch', false)) {
    final class sportsmanagementModelMatch
    {
        public static function getProjectPositionsOptions($id = 0, $personType = 1, $projectId = 0)
        {
            $app = Factory::getApplication();

            if (!$app instanceof SiteApplication) {
                throw new \RuntimeException('SportsManagement site application is unavailable.');
            }

            $model = new ResultsDataModel();
            $model->setDatabaseSelector(
                $app->getInput()->getInt('cfg_which_database', 0)
            );

            return $model->getProjectPositionsOptions(
                (int) $id,
                (int) $personType,
                (int) $projectId
            );
        }
    }
}
