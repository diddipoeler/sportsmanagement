<?php
/**
 * Legacy compatibility bridge for the native Stats view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Stats\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Stats/HtmlView.php';
}

if (!class_exists('sportsmanagementViewStats', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewStats');
}
