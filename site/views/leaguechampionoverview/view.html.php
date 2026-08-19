<?php
/**
 * Legacy compatibility bridge for the native Leaguechampionoverview view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Leaguechampionoverview\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Leaguechampionoverview/HtmlView.php';
}

if (!class_exists('sportsmanagementViewleaguechampionoverview', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewleaguechampionoverview');
}
