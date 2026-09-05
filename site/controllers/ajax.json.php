<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Ajax controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\AjaxController;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\AjaxModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;

// Upgraded installations can still have a stale Joomla namespace/autoload cache.
// Load the native AJAX dependency chain explicitly so the compatibility bridge
// remains functional even before Joomla has rebuilt that cache.
$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    AjaxModel::class => JPATH_SITE . '/components/com_sportsmanagement/src/Model/AjaxModel.php',
    AjaxController::class => JPATH_SITE . '/components/com_sportsmanagement/src/Controller/AjaxController.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(AjaxController::class)) {
    throw new \RuntimeException('SportsManagement native Ajax controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerAjax', false)) {
    class_alias(AjaxController::class, 'sportsmanagementControllerAjax');
}
