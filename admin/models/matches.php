<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Matches model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchesModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => __DIR__ . '/../src/Model/SportsManagementListModel.php',
    MatchesModel::class => __DIR__ . '/../src/Model/MatchesModel.php',
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

if (!class_exists(MatchesModel::class)) {
    throw new \RuntimeException('SportsManagement native Matches model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelMatches', false)) {
    class_alias(MatchesModel::class, 'sportsmanagementModelMatches');
}
