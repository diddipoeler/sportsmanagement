<?php
/**
 * Legacy compatibility bridge for the native Clubinfo view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Clubinfo\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Clubinfo/HtmlView.php';
}

if (!class_exists('sportsmanagementViewClubInfo', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewClubInfo');
}
