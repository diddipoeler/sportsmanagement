<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Databasetool model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel;

if (!class_exists(DatabasetoolModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/DatabasetoolModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(DatabasetoolModel::class)) {
    throw new \RuntimeException('SportsManagement native Databasetool model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeldatabasetool', false)) {
    class_alias(DatabasetoolModel::class, 'sportsmanagementModeldatabasetool');
}
