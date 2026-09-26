<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** SportsManagement legacy compatibility bridge for the Joomla 5/6 UEFA rating model. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\UefawertungModel;

if (!class_exists(UefawertungModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/UefawertungModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(UefawertungModel::class)) {
    throw new \RuntimeException('SportsManagement native UEFA rating model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeluefawertung', false)) {
    class_alias(UefawertungModel::class, 'sportsmanagementModeluefawertung');
}
