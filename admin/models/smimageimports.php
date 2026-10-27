<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Smimageimports model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportsModel;

if (!class_exists(SmimageimportsModel::class)) {
    $nativeFiles = [
        __DIR__ . '/../src/Model/SportsManagementListModel.php',
        __DIR__ . '/../src/Model/SmimageimportsModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (!is_file($nativeFile)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($nativeFile), 500);
        }

        require_once $nativeFile;
    }
}

if (!class_exists(SmimageimportsModel::class)) {
    throw new \RuntimeException('SportsManagement native Smimageimports model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsmimageimports', false)) {
    class_alias(SmimageimportsModel::class, 'sportsmanagementModelsmimageimports');
}
