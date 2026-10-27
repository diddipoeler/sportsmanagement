<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/PositionsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositionsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => __DIR__ . '/../src/Model/SportsManagementListModel.php',
    PositionsModel::class => __DIR__ . '/../src/Model/PositionsModel.php',
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

if (!class_exists(PositionsModel::class)) {
    throw new \RuntimeException('SportsManagement native Positions model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPositions', false)) {
    class_alias(PositionsModel::class, 'sportsmanagementModelPositions');
}
