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
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

if (!class_exists(ResultsDataModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(ResultsDataModel::class)) {
    throw new \RuntimeException('SportsManagement native ResultsData model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelMatch', false)) {
    final class sportsmanagementModelMatch
    {
        public static function getProjectPositionsOptions($id = 0, $personType = 1, $projectId = 0)
        {
            /** @var SiteApplication $app */
            $app = Factory::getContainer()->get(SiteApplication::class);

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
