<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Matchstaffstatistic model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstaffstatisticModel;

if (!class_exists(MatchstaffstatisticModel::class)) {
    foreach ([
        __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
        __DIR__ . '/../src/Model/MatchstaffstatisticModel.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(MatchstaffstatisticModel::class)) {
    throw new \RuntimeException('SportsManagement native Matchstaffstatistic model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelMatchstaffstatistic', false)) {
    class_alias(MatchstaffstatisticModel::class, 'sportsmanagementModelMatchstaffstatistic');
}
