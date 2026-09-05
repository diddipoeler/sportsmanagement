<?php
/**
 * SportsManagement legacy compatibility bridge for the native team stats model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamstatsModel;

if (!class_exists(TeamstatsModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/TeamstatsModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(TeamstatsModel::class)) {
    throw new \RuntimeException('SportsManagement native Teamstats model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTeamStats', false)) {
    class_alias(TeamstatsModel::class, 'sportsmanagementModelTeamStats');
}
