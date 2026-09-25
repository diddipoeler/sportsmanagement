<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Statsranking model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsrankingModel;

if (!class_exists(StatsrankingModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementStatsRankingModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/StatsrankingModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(StatsrankingModel::class)) {
    throw new \RuntimeException('SportsManagement native Statsranking model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelStatsRanking', false)) {
    class_alias(StatsrankingModel::class, 'sportsmanagementModelStatsRanking');
}
