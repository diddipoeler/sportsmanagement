<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/TeamsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => __DIR__ . '/../src/Model/SportsManagementListModel.php',
    TeamsModel::class => __DIR__ . '/../src/Model/TeamsModel.php',
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

if (!class_exists(TeamsModel::class)) {
    throw new \RuntimeException('SportsManagement native Teams model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTeams', false)) {
    class_alias(TeamsModel::class, 'sportsmanagementModelTeams');
}
