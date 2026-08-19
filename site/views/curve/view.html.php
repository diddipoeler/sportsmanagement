<?php
/**
 * Legacy compatibility bridge for the native Curve view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Curve\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Curve/HtmlView.php';
}

if (!class_exists('sportsmanagementViewCurve', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewCurve');
}
