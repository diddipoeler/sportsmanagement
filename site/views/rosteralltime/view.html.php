<?php
/**
 * Legacy compatibility bridge for the native Rosteralltime view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Rosteralltime\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Rosteralltime/HtmlView.php';
}

if (!class_exists('sportsmanagementViewRosteralltime', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewRosteralltime');
}
