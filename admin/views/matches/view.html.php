<?php
/** Legacy compatibility bridge for the native administrator matches view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Matches\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Matches/HtmlView.php';
}

if (!class_exists('sportsmanagementViewMatches', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewMatches');
}
