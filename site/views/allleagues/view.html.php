<?php
/** Legacy compatibility bridge for the native Joomla 5/6 Allleagues view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Allleagues\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Allleagues/HtmlView.php';
}

if (!class_exists('sportsmanagementViewallleagues', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewallleagues');
}
