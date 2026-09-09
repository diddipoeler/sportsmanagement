<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 all clubs model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllclubsModel;

if (!class_exists(AllclubsModel::class)) {
    $nativeModels = [
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllclubsModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(AllclubsModel::class)) {
    throw new \RuntimeException('SportsManagement native Allclubs model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelallclubs', false)) {
    class_alias(AllclubsModel::class, 'sportsmanagementModelallclubs');
}
