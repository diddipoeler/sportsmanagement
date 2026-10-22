<?php
/**
 * Results-view compatibility adapter for the legacy global Round model name.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;

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
