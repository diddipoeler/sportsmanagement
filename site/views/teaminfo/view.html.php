<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Teaminfo view.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Teaminfo\HtmlView;

if (!class_exists(HtmlView::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/View/Teaminfo/HtmlView.php',
    ] as $nativeView) {
        if (is_file($nativeView)) {
            require_once $nativeView;
        }
    }
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native Teaminfo view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewTeamInfo', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTeamInfo');
}
