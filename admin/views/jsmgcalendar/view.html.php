<?php
/** Legacy compatibility bridge for the native Joomla 5/6 Google Calendar edit view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgcalendar\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jsmgcalendar/HtmlView.php';
}

if (!class_exists('sportsmanagementViewjsmgcalendar', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjsmgcalendar');
}
