<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Quickadd model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\QuickaddModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => __DIR__ . '/../src/Model/SportsManagementListModel.php',
    QuickaddModel::class => __DIR__ . '/../src/Model/QuickaddModel.php',
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

if (!class_exists(QuickaddModel::class)) {
    throw new \RuntimeException('SportsManagement native Quickadd model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelQuickAdd', false)) {
    class_alias(QuickaddModel::class, 'sportsmanagementModelQuickAdd');
}
