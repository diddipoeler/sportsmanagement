<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 all clubs model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllclubsModel;

if (!class_exists(AllclubsModel::class)) {
    $nativeDependencies = [
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllclubsModel.php',
    ];

    foreach ($nativeDependencies as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(AllclubsModel::class)) {
    throw new \RuntimeException('SportsManagement native Allclubs model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelallclubs', false)) {
    class_alias(AllclubsModel::class, 'sportsmanagementModelallclubs');
}
