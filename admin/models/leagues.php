<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Leagues model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\LeaguesModel;

if (!class_exists(LeaguesModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/LeaguesModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(LeaguesModel::class)) {
    throw new \RuntimeException('SportsManagement native Leagues model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelLeagues', false)) {
    class_alias(LeaguesModel::class, 'sportsmanagementModelLeagues');
}
