<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextindividualsport model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextindividualsportModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
    JlextindividualsportModel::class => __DIR__ . '/../src/Model/JlextindividualsportModel.php',
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

if (!class_exists(JlextindividualsportModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextindividualsport model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextindividualsport', false)) {
    class_alias(JlextindividualsportModel::class, 'sportsmanagementModeljlextindividualsport');
}
