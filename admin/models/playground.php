<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Playground model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
    PlaygroundModel::class => __DIR__ . '/../src/Model/PlaygroundModel.php',
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

if (!class_exists(PlaygroundModel::class)) {
    throw new \RuntimeException('SportsManagement native Playground model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPlayground', false)) {
    class_alias(PlaygroundModel::class, 'sportsmanagementModelPlayground');
}
