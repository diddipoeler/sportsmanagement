<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 Agegroup model.
 *
 * The active implementation lives in admin/src/Model/AgegroupModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\AgegroupModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
    AgegroupModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/AgegroupModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(AgegroupModel::class)) {
    throw new \RuntimeException('SportsManagement native Agegroup model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelagegroup', false)) {
    class_alias(AgegroupModel::class, 'sportsmanagementModelagegroup');
}
