<?php
/** Legacy compatibility bridge for the native Allclubs view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Allclubs\HtmlView;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;

if (!class_exists(SportsManagementHtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Allclubs/HtmlView.php';
}

if (!class_exists('sportsmanagementViewallclubs', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewallclubs');
}
