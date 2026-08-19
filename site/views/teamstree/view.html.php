<?php
/**
 * Legacy compatibility bridge for the native Teamstree view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Teamstree\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Teamstree/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTeamsTree', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTeamsTree');
}
