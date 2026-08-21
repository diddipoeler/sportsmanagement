<?php
/** Legacy compatibility bridge for the native frontend Editteam view. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\View\Editteam\HtmlView;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;

if (!class_exists(SportsManagementHtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Editteam/HtmlView.php';
}

if (!class_exists('sportsmanagementViewEditteam', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewEditteam');
}
