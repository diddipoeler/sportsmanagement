<?php
/**
 * Legacy compatibility bridge for the native Clubplan view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Clubplan\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Clubplan/HtmlView.php';
}

if (!class_exists('sportsmanagementViewClubPlan', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewClubPlan');
}
