<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 matches slider helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    MatchesSliderHelper::class => __DIR__ . '/src/Helper/MatchesSliderHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    SportsManagementDatabaseResolver::class,
    MatchesSliderHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement MatchesSlider dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modMatchesSliderHelper', false)) {
    class_alias(MatchesSliderHelper::class, 'modMatchesSliderHelper');
}
