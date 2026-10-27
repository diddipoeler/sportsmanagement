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
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => __DIR__ . '/../src/Model/SportsManagementListModel.php',
    StatisticsModel::class => __DIR__ . '/../src/Model/StatisticsModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (class_exists($class, false)) {
        continue;
    }

    if (!is_file($file)) {
        throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($file), 500);
    }

    require_once $file;
}

if (!class_exists(StatisticsModel::class)) {
    throw new \RuntimeException('SportsManagement native Statistics model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelStatistics', false)) {
    class_alias(StatisticsModel::class, 'sportsmanagementModelStatistics');
}
