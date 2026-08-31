<?php
/** Legacy compatibility bridge for the native administrator Smquotes view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Smquotes\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Smquotes/HtmlView.php';
}

if (!class_exists('sportsmanagementViewsmquotes', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsmquotes');
}
