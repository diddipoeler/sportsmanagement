<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/LeagueModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\LeagueModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
    LeagueModel::class => __DIR__ . '/../src/Model/LeagueModel.php',
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

if (!class_exists(LeagueModel::class)) {
    throw new \RuntimeException('SportsManagement native League model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelleague', false)) {
    class_alias(LeagueModel::class, 'sportsmanagementModelleague');
}
