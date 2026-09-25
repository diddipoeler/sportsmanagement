<?php
/**
 * SportsManagement legacy compatibility bridge for the native iCal model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\IcalModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;

if (!class_exists(IcalModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/IcalModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(IcalModel::class)) {
    throw new \RuntimeException('SportsManagement native iCal model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelical', false)) {
    class_alias(IcalModel::class, 'sportsmanagementModelical');
}
