<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Teamplan view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Teamplan\HtmlView;

if (!class_exists(HtmlView::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/View/Teamplan/HtmlView.php',
    ] as $nativeView) {
        if (is_file($nativeView)) {
            require_once $nativeView;
        }
    }
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native Teamplan view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewTeamPlan', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTeamPlan');
}
