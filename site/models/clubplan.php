<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Clubplan model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubplanModel;

if (!class_exists(ClubplanModel::class)) {
    $nativeModels = [
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/ClubplanModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(ClubplanModel::class)) {
    throw new \RuntimeException('SportsManagement native Clubplan model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelClubPlan', false)) {
    class_alias(ClubplanModel::class, 'sportsmanagementModelClubPlan');
}
