<?php
/** Legacy compatibility bridge for the native Joomla 5/6 administrator Google calendars view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgcalendars\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jsmgcalendars/HtmlView.php';
}

if (!class_exists('sportsmanagementViewjsmgcalendars', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjsmgcalendars');
}
