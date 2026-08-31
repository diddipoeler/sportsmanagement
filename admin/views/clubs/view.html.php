<?php
/** Legacy compatibility bridge for the native administrator clubs view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Clubs\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Clubs/HtmlView.php';
}

if (!class_exists('sportsmanagementViewClubs', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewClubs');
}
