<?php
/** Legacy compatibility bridge for the native administrator Leagues view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Leagues\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Leagues/HtmlView.php';
}

if (!class_exists('sportsmanagementViewLeagues', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewLeagues');
}
