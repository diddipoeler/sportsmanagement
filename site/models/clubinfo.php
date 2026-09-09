<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 club info model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoModel;

if (!class_exists(ClubinfoModel::class)) {
    $nativeModels = [
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/ClubinfoModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(ClubinfoModel::class)) {
    throw new \RuntimeException('SportsManagement native Clubinfo model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelClubInfo', false)) {
    class_alias(ClubinfoModel::class, 'sportsmanagementModelClubInfo');
}
