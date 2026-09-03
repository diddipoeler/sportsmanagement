<?php
/**
 * SportsManagement legacy compatibility bridge for the native player model stack.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PlayerLegacyModel;

if (!class_exists(PlayerLegacyModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerMatchDataModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerStatisticsModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerTimeModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerLegacyModel.php';
}

if (!class_exists('sportsmanagementModelPlayer', false)) {
    class_alias(PlayerLegacyModel::class, 'sportsmanagementModelPlayer');
}
