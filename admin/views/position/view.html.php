<?php
/** Legacy compatibility bridge for the native Joomla 5/6 position view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Position\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Position/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPosition', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPosition');
}
