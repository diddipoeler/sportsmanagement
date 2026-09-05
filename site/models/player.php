<?php
/**
 * SportsManagement legacy compatibility bridge for the native player model stack.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PlayerLegacyModel;

if (!class_exists(PlayerLegacyModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerMatchDataModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerStatisticsModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerTimeModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerLegacyModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(PlayerLegacyModel::class)) {
    throw new \RuntimeException('SportsManagement native Player model stack could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPlayer', false)) {
    class_alias(PlayerLegacyModel::class, 'sportsmanagementModelPlayer');
}
