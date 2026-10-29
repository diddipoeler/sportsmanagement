<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Updates model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\UpdatesModel;

$nativeDependencies = [
    UpdatesModel::class => __DIR__ . '/../src/Model/UpdatesModel.php',
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

if (!class_exists(UpdatesModel::class)) {
    throw new \RuntimeException('SportsManagement native Updates model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelUpdates', false)) {
    class_alias(UpdatesModel::class, 'sportsmanagementModelUpdates');
}
