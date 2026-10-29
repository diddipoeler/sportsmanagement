<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Sportstype model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportstypeModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
    SportstypeModel::class => __DIR__ . '/../src/Model/SportstypeModel.php',
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

if (!class_exists(SportstypeModel::class)) {
    throw new \RuntimeException('SportsManagement native Sportstype model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsportstype', false)) {
    class_alias(SportstypeModel::class, 'sportsmanagementModelsportstype');
}
