<?php
/**
 * SportsManagement legacy compatibility bridge for the native age-groups model.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/AgegroupsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\AgegroupsModel;

if (!class_exists(AgegroupsModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/AgegroupsModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(AgegroupsModel::class)) {
    throw new \RuntimeException('SportsManagement native Agegroups model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelagegroups', false)) {
    class_alias(AgegroupsModel::class, 'sportsmanagementModelagegroups');
}
