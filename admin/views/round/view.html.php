<?php
/** Legacy compatibility bridge for the native Joomla 5/6 round administrator view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Round\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Round/HtmlView.php';
}

if (!class_exists('sportsmanagementViewRound', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewRound');
}
