<?php
/**
 * Legacy compatibility bridge for the native administrator Statistics model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\StatisticsModel;

if (!class_exists(StatisticsModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/StatisticsModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(StatisticsModel::class)) {
    throw new \RuntimeException('SportsManagement native Statistics model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelStatistics', false)) {
    class_alias(StatisticsModel::class, 'sportsmanagementModelStatistics');
}
