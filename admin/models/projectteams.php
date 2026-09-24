<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Projectteams list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel;

if (!class_exists(ProjectteamsModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/ProjectRelationService.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectteamsModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(ProjectteamsModel::class)) {
    throw new \RuntimeException('SportsManagement native Projectteams model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProjectteams', false)) {
    class_alias(ProjectteamsModel::class, 'sportsmanagementModelProjectteams');
}
