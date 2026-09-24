<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextcountries list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextcountriesModel;

if (!class_exists(JlextcountriesModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextcountriesModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(JlextcountriesModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextcountries model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextcountries', false)) {
    class_alias(JlextcountriesModel::class, 'sportsmanagementModeljlextcountries');
}
