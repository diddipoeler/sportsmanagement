<?php
/**
 * SportsManagement legacy compatibility bridge for the native Allplaygrounds model.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllplaygroundsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllplaygroundsModel;

if (!class_exists(AllplaygroundsModel::class)) {
    $nativeModels = [
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllplaygroundsModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(AllplaygroundsModel::class)) {
    throw new \RuntimeException('SportsManagement native Allplaygrounds model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelallplaygrounds', false)) {
    class_alias(AllplaygroundsModel::class, 'sportsmanagementModelallplaygrounds');
}
