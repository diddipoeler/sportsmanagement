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
    SportsManagementDatabaseResolver::class => __DIR__ . '/../src/Service/SportsManagementDatabaseResolver.php',
    SiteRouteHelper::class => __DIR__ . '/../src/Helper/SiteRouteHelper.php',
    AjaxModel::class => __DIR__ . '/../src/Model/AjaxModel.php',
    AjaxController::class => __DIR__ . '/../src/Controller/AjaxController.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (class_exists($class)) {
        continue;
    }

    if (!is_file($file)) {
        throw new \RuntimeException('SportsManagement Ajax dependency file is missing: ' . basename($file), 500);
    }

    require_once $file;
}

foreach (array_keys($nativeDependencies) as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Ajax dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('sportsmanagementControllerAjax', false)) {
    class_alias(AjaxController::class, 'sportsmanagementControllerAjax');
}
