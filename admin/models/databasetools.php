<?php
/**
 * Legacy compatibility bridge for the native administrator Databasetools model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolsModel;

if (!class_exists(DatabasetoolsModel::class)) {
    $nativeModels = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/DatabasetoolsModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(DatabasetoolsModel::class)) {
    throw new \RuntimeException('SportsManagement native Databasetools model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelDatabaseTools', false)) {
    class_alias(DatabasetoolsModel::class, 'sportsmanagementModelDatabaseTools');
}
