<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 Top Tipper module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Module\SportsManagementTopTipper\Site\Helper\TopTipperHelper;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    TopTipperHelper::class => __DIR__ . '/src/Helper/TopTipperHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    TopTipperHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Top Tipper dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMTopTipper', false)) {
    class_alias(TopTipperHelper::class, 'modJSMTopTipper');
}
