<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Ajax model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AjaxModel;

if (!class_exists(AjaxModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/AjaxModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(AjaxModel::class)) {
    throw new \RuntimeException('SportsManagement native Ajax model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelAjax', false)) {
    class_alias(AjaxModel::class, 'sportsmanagementModelAjax');
}
