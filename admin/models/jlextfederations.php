<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextfederations list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextfederationsModel;

if (!class_exists(JlextfederationsModel::class)) {
    $nativeFiles = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextfederationsModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(JlextfederationsModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextfederations model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextfederations', false)) {
    class_alias(JlextfederationsModel::class, 'sportsmanagementModeljlextfederations');
}
