<?php
/**
 * Legacy compatibility bridge for the native Allprojects view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Allprojects\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Allprojects/HtmlView.php';
}

if (!class_exists('sportsmanagementViewallprojects', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewallprojects');
}
