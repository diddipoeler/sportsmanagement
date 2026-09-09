<?php
/**
 * Legacy compatibility bridge for the native Allprojects model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllprojectsModel;

if (!class_exists(AllprojectsModel::class)) {
    $nativeModels = [
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllprojectsModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(AllprojectsModel::class)) {
    throw new \RuntimeException('SportsManagement native Allprojects model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelallprojects', false)) {
    class_alias(AllprojectsModel::class, 'sportsmanagementModelallprojects');
}
