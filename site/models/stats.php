<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Stats model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsModel;

if (!class_exists(StatsModel::class)) {
    $databaseResolver = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php';
    $applicationResolver = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';
    $baseModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    $projectModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/StatsModel.php';

    foreach ([$databaseResolver, $applicationResolver, $baseModel, $projectModel, $nativeModel] as $modelFile) {
        if (is_file($modelFile)) {
            require_once $modelFile;
        }
    }
}

if (!class_exists(StatsModel::class)) {
    throw new \RuntimeException('SportsManagement native Stats model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelStats', false)) {
    class_alias(StatsModel::class, 'sportsmanagementModelStats');
}
