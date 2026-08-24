<?php
/** Legacy compatibility bridge for the native Joomla 5/6 roster-position view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Rosterposition\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Rosterposition/HtmlView.php';
}

if (!class_exists('sportsmanagementViewrosterposition', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewrosterposition');
}
