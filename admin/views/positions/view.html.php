<?php
/** Legacy compatibility bridge for the native administrator Positions view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Positions\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Positions/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPositions', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPositions');
}
