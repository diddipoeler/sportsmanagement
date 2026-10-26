<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Google calendar model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarModel;

if (!class_exists(JsmgcalendarModel::class)) {
    foreach ([
        __DIR__ . '/../src/Helper/ActionLogHelper.php',
        __DIR__ . '/../src/Helper/SportsManagementDatabaseResolver.php',
        __DIR__ . '/../src/Service/SportsManagementAdministratorApplicationResolver.php',
        __DIR__ . '/../src/Table/SportsManagementTable.php',
        __DIR__ . '/../src/Table/JsmgcalendarTable.php',
        __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
        __DIR__ . '/../src/Model/JsmgcalendarModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(JsmgcalendarModel::class)) {
    throw new \RuntimeException('SportsManagement native Jsmgcalendar model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljsmGCalendar', false)) {
    class_alias(JsmgcalendarModel::class, 'sportsmanagementModeljsmGCalendar');
}
