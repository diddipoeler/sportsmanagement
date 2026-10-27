<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Projectteams model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectRelationService;

$nativeDependencies = [
    SportsManagementListModel::class => __DIR__ . '/../src/Model/SportsManagementListModel.php',
    ProjectRelationService::class => __DIR__ . '/../src/Service/ProjectRelationService.php',
    ProjectteamsModel::class => __DIR__ . '/../src/Model/ProjectteamsModel.php',
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

if (!class_exists(ProjectteamsModel::class)) {
    throw new \RuntimeException('SportsManagement native Projectteams model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProjectteams', false)) {
    class_alias(ProjectteamsModel::class, 'sportsmanagementModelProjectteams');
}
