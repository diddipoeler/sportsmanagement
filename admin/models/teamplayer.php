<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 team player model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayerModel;

if (!class_exists(TeamplayerModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TeamplayerModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(TeamplayerModel::class)) {
    throw new \RuntimeException('SportsManagement native Teamplayer model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelteamplayer', false)) {
    class_alias(TeamplayerModel::class, 'sportsmanagementModelteamplayer');
}
