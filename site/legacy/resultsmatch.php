<?php
/**
 * Results-view compatibility adapter for the one legacy Match helper still
 * used by the frontend results form.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;

if (!class_exists(ResultsDataModel::class) || !class_exists(SportsManagementSiteApplicationResolver::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(ResultsDataModel::class) || !class_exists(SportsManagementSiteApplicationResolver::class)) {
    throw new \RuntimeException('SportsManagement native results runtime could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelMatch', false)) {
    final class sportsmanagementModelMatch
    {
        public static function getProjectPositionsOptions($id = 0, $personType = 1, $projectId = 0)
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement results compatibility adapter requires the Joomla site application.', 500);
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
