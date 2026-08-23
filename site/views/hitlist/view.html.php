<?php
/** Legacy compatibility bridge for the native Joomla 5/6 hitlist view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Hitlist\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Hitlist/HtmlView.php';
}

if (!class_exists('sportsmanagementViewhitlist', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewhitlist');
}
