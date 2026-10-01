<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Projects model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
    ProjectsModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectsModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(ProjectsModel::class)) {
    throw new \RuntimeException('SportsManagement native Projects model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProjects', false)) {
    class_alias(ProjectsModel::class, 'sportsmanagementModelProjects');
}
