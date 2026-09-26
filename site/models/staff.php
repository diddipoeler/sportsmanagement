<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Staff model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StaffModel;

if (!class_exists(StaffModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PersonModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/StaffModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(StaffModel::class)) {
    throw new \RuntimeException('SportsManagement native Staff model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelStaff', false)) {
    class_alias(StaffModel::class, 'sportsmanagementModelStaff');
}
