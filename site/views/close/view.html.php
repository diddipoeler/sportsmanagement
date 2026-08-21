<?php
/**
 * SportsManagement legacy site view bridge.
 * The active Joomla 5/6 implementation lives in site/src/View/Close/HtmlView.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Close\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Close/HtmlView.php';
}

if (!class_exists('sportsmanagementViewClose', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewClose');
}
